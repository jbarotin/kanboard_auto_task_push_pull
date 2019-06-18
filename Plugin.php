<?php

namespace Kanboard\Plugin\auto_task_push_pull;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Plugin\auto_task_push_pull\Action\AutoPullTask;

class Plugin extends Base
{
    public function initialize()
    {
      $this->actionManager->register(new AutoPullTask($this->container));
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'Automatic Task Push/Pull';
    }

    public function getPluginDescription()
    {
        return t('This plugin aim to add an automatic action aim to push and pull tasks to an other cols if the limit is reached. In order to automatically apply the kanban workflow.');
    }

    public function getPluginAuthor()
    {
        return 'Jerome Barotin';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/jbarotin/kanboard_auto_task_push_pull';
    }
}
