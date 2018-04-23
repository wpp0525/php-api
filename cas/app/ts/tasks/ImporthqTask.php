<?php
// 好巧数据导入任务

use Phalcon\CLI\Task;

class ImporthqTask extends Task
{

    public function setDI(Phalcon\DiInterface $dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $this->service = new \ImportHaoqiaoService($dependencyInjector);
        //$this->dist_delete = new \ProductPoolRedisV2DelService($dependencyInjector);
    }

    public function matchZhAction($params)
    {
        $this->service->matchZh($params);
    }

    public function matchEnAction($params)
    {
        $this->service->matchEn($params);
    }

    public function importAction($params)
    {
        $this->service->import($params);
    }

}
