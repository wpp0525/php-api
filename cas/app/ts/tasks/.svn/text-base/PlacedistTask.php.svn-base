<?php 
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
* place产品多出发地更新关系
*/
class PlacedistTask extends Task
{
	/**
     * @var ProductPoolDataService
     */
    private $biz_place;

    /**
     * @var ProductPoolDataService
     */
//    private $pp_plac;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->biz_place = new \PlacedistWorkerService($dependencyInjector);
//        $this->pp_product_dest_rel = $dependencyInjector->get('cas')->get('pp_product_dest_rel');
    }

    /**
     * @example php ../ts.php Placedist sendMsgPlaceDist
     */
    public function sendMsgPlaceDistAction() {
        $this->biz_place->process();
    }
}
