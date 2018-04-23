<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 *
 */
class BizDestWorkerService implements DaemonServiceInterface {

    /*
     * 基础库
     * @var DestinBaseDataService
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
    private $redis_cache_key = 'dest_multi_relation_jack';

    public $result = array(
        'dest_id' => 0,
        'parent_id' => 0,
        'district_type' => '',
        'continent_id' => 0,
        'country_id' => 0,
        'province_id' => 0,
        'city_id' => 0,
        'county_id' => 0,
        'span_city_id' => 0,
        'span_country_id' => 0,
        'span_province_id' => 0
    );

    /**
     * 递归目的地类型
     * @var array
     */
    public $dest_type_list = array(
        "CITY",
        "CONTINENT",
        "COUNTRY",
        "PROVINCE",
        "SPAN_CITY",
        "SPAN_COUNTRY",
        "SPAN_PROVINCE",
        "COUNTY",
        "TOWN"
    );

    public function __construct($di) {
        $this->distin_base = $di->get('cas')->get('destin_base_service');
        //$this->distin_base->setReconnect(true);

        $this->destin_multi_relation_base = $di->get('cas')->get('destin_multi_relation_base_service');
        //$this->destin_multi_relation_base->setReconnect(true);


        $this->redis = $di->get('cas')->getRedis();

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    public function process ($a=null,$b='c')
    {
	    while(1) {


	        // 分页条数
	        $limit = 1000;

	        // 获取游标
	        $last_id = $this->getLastId();

	        $where = " cancel_flag = 'Y' and dest_id > $last_id ";

	        // 待优化

	        $dest_infos = $this->distin_base->getDefaultList($where, $limit);

	        if ( empty($dest_infos) ) {
	            //重置游标
	            $this->setLastId(0);
	            $this->stopFlag();
	        }

	        foreach ($dest_infos as $dest_infos_value) {

	            echo '游标值:' . $this->getLastId() . PHP_EOL;
	            echo 'dest_id: ' . $dest_infos_value['dest_id'] . PHP_EOL;


	            $this->result['dest_id'] = $dest_infos_value['dest_id'];

	            $this->getParent($dest_infos_value);

	            if ( !empty( $this->result['town_id'] ) ) {
	                $this->destin_multi_relation_base->update($this->result['dest_id'], array('town_id' => $this->result['town_id']));
	            }

	            // 更新游标
	            $this->setLastId($dest_infos_value['dest_id']);

	            unset($this->result);
	            unset($result_format);

	            // usleep(200);
	        }
	    }

    }

    /**
     * 递归查询上级节点信息
     * @param $data
     * @return array
     */
    private function getParent($data)
    {
        $parent_id = $data['parent_id'];

        $data = $this->distin_base->getOneById($parent_id);

        if ( empty($data) ) {
            return $this->result;
        } else {

            if ( in_array( $data['dest_type'],$this->dest_type_list ) ) {
                // 数据库列和字段值
                $column_name = strtolower($data['dest_type']) . '_id';
                $column_id = $data['dest_id'];

                $this->result[$column_name] = $column_id;
            }

            $this->getParent($data);

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
        exit('程序跑完了，回家吃饭!');
    }

}