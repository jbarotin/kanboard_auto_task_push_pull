<?php

namespace Kanboard\Plugin\auto_task_push_pull\Action;
set_include_path(get_include_path().":"."/home/jerome/github/kanboard/" );
require_once 'tests/units/Base.php';
require_once '../Plugin.php';

use Kanboard\Plugin\auto_task_push_pull\Plugin;



class PluginTest extends Base
{
    public function testPlugin()
    {

      $projectModel = new ProjectModel($this->container);
      $taskCreationModel = new TaskCreationModel($this->container);
      $taskFinderModel = new TaskFinderModel($this->container);
      $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
      $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'test')));
      $this->assertEquals(2, $taskCreationModel->create(array('project_id' => 1, 'title' => 'test', 'column_id' => 3)));
      $this->assertEquals(3, $taskCreationModel->create(array('project_id' => 1, 'title' => 'test', 'column_id' => 2)));
      $this->container['db']->table(TaskModel::TABLE)->in('id', array(2, 3))->update(array('date_due' => strtotime('-10days')));


      $plugin = new Plugin($this->container);
      $this->assertSame(null, $plugin->initialize());
      $this->assertSame(null, $plugin->onStartup());
      $this->assertNotEmpty($plugin->getPluginName());
      $this->assertNotEmpty($plugin->getPluginDescription());
      $this->assertNotEmpty($plugin->getPluginAuthor());
      $this->assertNotEmpty($plugin->getPluginVersion());
      $this->assertNotEmpty($plugin->getPluginHomepage());

    }
}
