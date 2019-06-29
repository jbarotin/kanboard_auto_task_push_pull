<?php
namespace Kanboard\Plugin\auto_task_push_pull\Action;

use Kanboard\Action\Base;
use Kanboard\Model\TaskModel;


class AutoPushPullTask extends Base {
  /**
   * Get automatic action description
   *
   * @access public
   * @return string
   */
  public function getDescription()
  {
      return t('Automatically push/pull a task from an other column if task number is reached');
  }

  /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_MOVE_COLUMN,
        );
    }

    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array(
            'src_column_id' => t('Column Source'),
            'dest_column_id' => t('Column Destination')
        );
    }

    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array(
            'task' => array(
                'column_id',
                'project_id',
            ),
        );
    }

    private $debug = false;
    private function debug($string){
      if($this->debug){
        $this->logger->debug($string);
      }
    }

    private function getColumn($column_id, $project_id){
      $columns = $this->columnModel->getAllWithTaskCount($project_id);
      for ($i = 0; $i < count($columns); $i++) {
        if($columns[$i]["id"] == $column_id){
          return $columns[$i];
        }
      }
    }

    private function isColumnFull($column_id, $project_id){
      $column = $this->getColumn($column_id, $project_id);
      $title = $column["title"];
      $this->debug("check if ".$title." is full");
      if($column["task_limit"] > 0){
        if($column["nb_open_tasks"] > $column["task_limit"]){
          $this->debug($title." is full nb_open_tasks : ".$column["nb_open_tasks"]." > task_limit : ".$column["task_limit"]);
          return true;
        }
      }
      $this->debug($title." is not full nb_open_tasks : ".$column["nb_open_tasks"]." > task_limit : ".$column["task_limit"]);
      return false;
    }

    private function isTaskAvailableInCol($column_id, $project_id){
      $column = $this->getColumn($column_id, $project_id);
      $title = $column["title"];
      $this->debug("column ".$title." have ".$column["nb_open_tasks"]." opened tasks");
      return $column["nb_open_tasks"] > 0;
    }

    private function get_first_task_id_in_col($project_id, $column_id){
      $result = $this->db->table(TaskModel::TABLE)
          ->eq('project_id', $project_id)
          ->eq('column_id', $column_id)
          ->columns('MIN(position) AS pos')
          ->findOne();

      $this->debug(print_r($result, true));

      return $this->get_task_id_at_pos_in_col($project_id, $column_id, $result["pos"]);
    }

    private function get_task_id_at_pos_in_col($project_id, $column_id, $pos){
      $result = $this->db->table(TaskModel::TABLE)
          ->eq('project_id', $project_id)
          ->eq('column_id', $column_id)
          ->eq('position', $pos)
          ->columns('id')
          ->findOne();
      return $result["id"];
    }

    private function get_last_task_id_in_col($project_id, $column_id){
      $result = $this->db->table(TaskModel::TABLE)
          ->eq('project_id', $project_id)
          ->eq('column_id', $column_id)
          ->columns('MAX(position) AS pos')
          ->findOne();

      $this->debug(print_r($result, true));

      return $this->get_task_id_at_pos_in_col($project_id, $column_id, $result["pos"]);
    }

    private function get_last_post($project_id, $column_id){
      $result = $this->db->table(TaskModel::TABLE)
          ->eq('project_id', $project_id)
          ->eq('column_id', $column_id)
          ->columns('MAX(position) AS pos')
          ->findOne();
      return $result["pos"]+1;
    }


    private function get_first_pos($project_id, $column_id){
      $result = $this->db->table(TaskModel::TABLE)
          ->eq('project_id', $project_id)
          ->eq('column_id', $column_id)
          ->columns('MIN(position) AS pos')
          ->findOne();
      return $result["pos"];
    }

    /**
   * Execute the action
   *
   * @access public
   * @param  array   $data   Event data dictionary
   * @return bool            True if the action was executed or false when not executed
   */
  public function doAction(array $data)
  {

      $project_id = $data['task']['project_id'];
      $src_column = $this->getColumn($this->getParam('src_column_id'), $project_id);
      $dest_column = $this->getColumn($this->getParam('dest_column_id'), $project_id);

      //pull
      while($this->isSrcToDestConditons($data)){
        $this->debug("hasSrcToDestConditons");
        $top_task_id = $this->get_first_task_id_in_col($project_id, $this->getParam('src_column_id'));
        $this->debug("move ".$top_task_id." from ".$src_column["title"]." to ".$dest_column["title"]." opened tasks");
        $this->taskPositionModel->movePosition(
            $data['task']['project_id'],
            $top_task_id,
            $this->getParam('dest_column_id'),
            $this->get_last_post($project_id, $this->getParam('dest_column_id')),
            0,
            false
        );
      }

      //push
      while($this->isDestToSrcConditons($data)){
        $this->debug("hasDestToSrcConditons");
        $top_task_id = $this->get_last_task_id_in_col($project_id, $this->getParam('dest_column_id'));
        $this->debug("move ".$top_task_id." from '".$dest_column["title"]."' to '".$src_column["title"]."' opened tasks");
        $this->taskPositionModel->movePosition(
            $data['task']['project_id'],
            $top_task_id,
            $this->getParam('src_column_id'),
            $this->get_first_pos($project_id, $this->getParam('src_column_id')),
            0,
            false
        );
     }


      return true;
  }

  /*
  SRC is not full and DST have task to push
  */
  private function isSrcToDestConditons(array $data)
  {
      return !$this->isColumnFull($this->getParam('dest_column_id'), $data["project_id"])
              && $this->isColumnFull($this->getParam('src_column_id'), $data["project_id"]);
  }

  /*
  SRC is not full and DST is full
  */
  private function isDestToSrcConditons(array $data)
  {
      return $this->isColumnFull($this->getParam('dest_column_id'), $data["project_id"]);
  }

  /**
   * Check if the event data meet the action condition
   *
   * @access public
   * @param  array   $data   Event data dictionary
   * @return bool
   */
  public function hasRequiredCondition(array $data)
  {
    return $this->isSrcToDestConditons($data) || $this->isDestToSrcConditons($data);
  }
}

?>
