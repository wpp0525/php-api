<?php 
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
* place产品多出发地更新关系
*/
class Bbstotravel2Task extends Task
{
	/**
     * @var
     */
    private $bbs_to_travel;
    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->bbs_to_travel = new \BbsToTravelWorker2Service($dependencyInjector);
    }

    /**
     * @example php ../ts.php Bbstotravel2 sendMsgPlaceDist
     */
    public function sendMsgPlaceDistAction() {
        $this->bbs_to_travel->process();
    }
}
