<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author 洪武极
 *
 */
class ImageWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $dest_base;
    private $image_svc;
    private $dest_rel;
    public function __construct($di) {
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->image_svc =$di->get('cas')->get('dest_image_service');
        $this->dest_rel =$di->get('cas')->get('dest_relation_service');
        $this->dest_base->setReconnect(true);
        $this->image_svc->setReconnect(true);
        $this->dest_rel->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $total=$this->image_svc->getDestAllImages();
        $total_page=ceil($total/300);
        $this->excuteData(1,300,$total_page);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    private function excuteData($page_num=1,$page_size=300,$total_page)
    {
        if ($page_num <= $total_page) {
            $result = $this->image_svc->getImageList(array('page_num' => $page_num, 'page_size' => $page_size));
            if ($result) {
                foreach ($result as $key => $value) {
                    $parent_ids=$this->dest_base->getDestParents($value['object_id']);
                    if($parent_ids){
                        foreach($parent_ids as $k=>$id){
                            $insert_data=array(
                                'dest_id'=>$id,
                                'image_id'=>$value['image_id']
                            );
                            $this->dest_rel->insert($insert_data,'relation_dest_image');
                            unset($insert_data);
                        }
                    }
                    $insert_data=array(
                        'dest_id'=>$value['object_id'],
                        'image_id'=>$value['image_id']
                    );
                    $this->dest_rel->insert($insert_data,'relation_dest_image');
                    unset($insert_data);
                }
                unset($result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteData($current_page, $page_size, $total_page);
            }
        } else {
            echo 'job done!';
            exit;
        }
    }
}