<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地新增数据task
 *
 * @author jackdong
 *
 */
class BizDestTempService implements DaemonServiceInterface
{

    /*
     * 基础库
     * @var \Lvmama\Cas\Service\DestinBaseDataService;
     */
    private $distin_base;

    /**
     * @var destin_multi_relation_base_service
     */
    private $destin_multi_relation_base;

    /**
     * redis service
     * @var
     */
    private $redis;

    /**
     * redis key
     * @var string
     */
    private $redis_cache_key = 'biz_dest_temp_jack';


    public function __construct($di)
    {
        /**
         * @var \Lvmama\Cas\Service\DestinBaseDataService;
         */
        $this->distin_base = $di->get('cas')->get('destin_base_service');
        $this->distin_base->setReconnect(true);

//        $this->destin_multi_relation_base = $di->get('cas')->get('destin_multi_relation_base_service');
//        $this->destin_multi_relation_base->setReconnect(true);


        $this->redis = $di->get('cas')->getRedis();

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    public function process ( $kafka=null,$b='c' )
    {

        while(1) {


            // 分页条数
            $limit = 1000;

            // 获取游标
            $last_id = $this->getLastId();

    //        $where = " cancel_flag = 'Y' and dest_id > $last_id ";
            $where = " dest_id > $last_id ";

            $dest_infos = $this->distin_base->getDefaultListByTableName($where, $limit);

            if ( empty($dest_infos) ) {
                //重置游标
                $this->setLastId(0);
                $this->stopFlag();
            }

            foreach ($dest_infos as $dest_infos_value)
            {
                $dest_id_flag = $dest_infos_value['dest_id'];
                unset($dest_infos_value['dest_id']);

                echo '游标值:' . $this->getLastId() . PHP_EOL;

                $insert_data = array();
                $tmp = $this->distin_base->getOneByOtherDestInfo( $dest_infos_value['dest_name'], $dest_infos_value['dest_type'], $dest_infos_value['district_id'] );

                if ( empty($tmp) ) {
//                    $this->distin_base->insert( $dest_infos_value );

                    // 新增标识位
                    $dest_infos_value['doActionJack'] = 'insertNew';

                    $dest_infos_value_str = json_encode($dest_infos_value);
                    $kafka->sendMsg($dest_infos_value_str);
                }

                // 更新游标
                $this->setLastId( $dest_id_flag );

                usleep(200);

            }
        }

    }

    /**
     * 设置游标
     * @param $id
     * @return mixed
     */
    public function setLastId($id)
    {
        $result = $this->redis->set($this->redis_cache_key,$id,3600);
        return $result;
    }

    /**
     * 获取游标
     * @return mixed
     */
    public function getLastId()
    {
        $result = $this->redis->get($this->redis_cache_key);

        if ( empty($result) ) {
            $this->redis->set($this->redis_cache_key,0,3600);
            $result = $this->redis->get($this->redis_cache_key);
        }

        return $result;


    }

    private function stopFlag()
    {
        $this->distin_base->disconnect();
        exit('程序跑完了，回家吃饭!');
    }

}