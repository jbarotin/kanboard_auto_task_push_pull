<?php
namespace Kanboard\Plugin\auto_task_push_pull\Action;

use Kanboard\Action\Base;
use Kanboard\Model\TaskModel;


class AutoPullTask extends Base {
  /**
   * Get automatic action description
   *
   * @access public
   * @return string
   */
  public function getDescription()
  {
      return t('Automatically pull a task from an other column if task number is reached');
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
            'dest_column_id' => t('Column Source'),
            'src_column_id' => t('Column Destination')
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

    private function columnCount($column_id, $project_id){
      $columns = $this->columnModel->getAllWithTaskCount($project_id)
      print_r($columns)
      foreach ($columns as $key => $value) {
        // code...
      }
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
      $values = array(
          'dest_column_id' => $this->getParam('dest_column_id'),
          'src_column_id' => $this->getParam('src_column_id'),
      );
      return $this->taskModificationModel->update($values, false);
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
    //$columnModel
    //check "nb task dans la colonne dest" > "max"
      return $data['task']['column_id'] == $this->getParam('column_id');
  }
}

?>
