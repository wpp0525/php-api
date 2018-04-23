<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 行政区数据 服务类
 *
 * @author flash.guo
 *
 */
class DistBaseIpService extends DataServiceBase {

//    const TABLE_NAME = 'biz_district';//对应数据库表
//    const PRIMARY_KEY = 'district_id'; //对应主键，如果有
    public $table_name = 'biz_district_ip_20170413';
    const PV_REAL = 2;
    const LIKE_INIT = 3;
    private $district_type;

    public function __construct($di, $adapter, $redis = null, $beanstalk = null) {
        $this->di = $di;
        $this->adapter = $adapter;
        $this->redis = $redis;
        $this->beanstalk = $beanstalk;
        $this->get_table_name();
        $this->district_type = array(
            'CITY','CONTINENT','COUNTRY','COUNTY','PROVINCE','PROVINCE_AN','PROVINCE_DCG','PROVINCE_SA','TOWN'
        );
    }

    public function get_table_name ()
    {
        $table_suffix = 'biz_district_ip_';

        $current_date = date('Ymd',time());
        $table_name = $table_suffix . $current_date;

        // 获取最近 30天内的最新表
        for ( $i = 1 ;$i <= 31; $i++ ) {
            $tmp_date = $current_date - $i;
            $tmp_table_name = $table_suffix . $tmp_date;
            $sql = "SHOW TABLES LIKE '$tmp_table_name'";
            $result = $this->getAdapter()->query($sql)->fetch();
            if ( $result ) {
                $this->table_name = $tmp_table_name;
                break;
            }
        }

    }

    /**
     * 添加行政区数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert($this->table_name, array_values($data), array_keys($data));
    }

    /**
     * 更新行政区数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($country, $data) {
        usleep(400);
        $whereCondition = "Country =  '$country'";
        return $this->getAdapter()->update($this->table_name, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取行政区数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDistList($where_condition, $limit = NULL, $columns = "*", $order = NULL){
        $data=$this->getList($where_condition, $this->table_name, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取行政区总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getDistTotal($where_condition){
        $data=$this->getTotalBy($where_condition, $this->table_name);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条行政区数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneDist($where_condition){
        $data=$this->getOne($where_condition, $this->table_name);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条行政区数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('district_id'=>"=".$id);
        $data=$this->getOne($where_condition, $this->table_name);
        return $data?$data:false;
    }

    /**
     * ip段匹配
     * @param $ip_num
     * @return bool|mixed
     */
    public function getOneByIpNum($ip_num){
        $where_condition =  "StartIPNum <= '$ip_num' and EndIPNum >= '$ip_num' ";
        $columns = 'district_id';
        $data=$this->getOne($where_condition, $this->table_name,$columns);
        return $data?$data:false;
    }
    /**
     * 将指定类型下的行政区ID
     * @param $district 行政区基本信息
     * @param $type 上推的最小类型行政区的类型
     * @return int
     * @author shenxiang
     */
    public function getDistrictIdForType($district,$type = ''){
        $district_id = $district['district_id'];
        //如果传入的参数不在指定的类型里面
        if(!in_array($type,$this->district_type)) return $district_id;
        $in_type = array();
        switch($type){
            case 'CITY':
                $in_type[] = 'TOWN';
                $in_type[] = 'COUNTY';
                break;
            default:
        }
        $dist_base_service = $this->di->get('cas')->get('dist_base_service');
        while(in_array($district['district_type'],$in_type)){
            $district = $dist_base_service->getOneById($district['parent_id']);
            $district_id = $district['district_id'];
        }
        return $district_id;
    }
}