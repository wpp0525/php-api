<?php 
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
* 产品与目的地关系
*/
class BizdestTask extends Task
{
	/**
     * @var DestProductRelV2Service
     */
    private $biz_dest;

    /**
     * @var PpProductDestRelService
     */
    private $pp_product_dest_rel;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->biz_dest = new \BizDestWorkerService($dependencyInjector);
        $this->pp_product_dest_rel = $dependencyInjector->get('cas')->get('pp_product_dest_rel');
    }

    /**
     * @example php ../ts.php Bizdest sendMsgBizDest
     */
    public function sendMsgBizDestAction() {
        $this->biz_dest->process();
    }
}
