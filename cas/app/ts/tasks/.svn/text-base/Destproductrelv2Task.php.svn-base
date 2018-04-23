<?php 
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
* 产品与目的地关系
*/
class Destproductrelv2Task extends Task
{
	/**
     * @var DestProductRelV2Service
     */
    private $dest_product_rel_v2;

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
        $this->dest_product_rel_v2 = new \DestProductRelV2WorkerService($dependencyInjector);
        $this->pp_product_dest_rel = $dependencyInjector->get('cas')->get('pp_product_dest_rel');
    }

    /**
     * @example php ../ts.php Destproductrelv2 sendMsgDestProductRelV2
     */
    public function sendMsgDestProductRelV2Action() {
        $pages = $this->pp_product_dest_rel->getProductDestRelsTotal();
        $page_size = 1000;
        $total = ceil($pages / $page_size);

        for($i = 0; $i < $pages; $i++){
            $data = array();
            $product_dest_list = $this->pp_product_dest_rel->getProductDestRels($i * $page_size, $page_size);
            if(!empty($product_dest_list)){
                foreach($product_dest_list as $key => $item){
                    $data[$key]['product_id'] = $item['PRODUCT_ID'];
                    $data[$key]['dest_id'] = $item['DEST_IDS'];
                    $data[$key]['category_id'] = $item['CATEGORY_ID'];
                    $data[$key]['sub_category_id'] = !empty($item['SUB_CATEGORY_ID']) ? $item['SUB_CATEGORY_ID'] : 0;
                }
                
                $this->dest_product_rel_v2->process(null, null, json_encode($data));
            }
        }
    }
}
