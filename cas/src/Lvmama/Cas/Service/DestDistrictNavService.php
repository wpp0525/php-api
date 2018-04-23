<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 
 *
 * @author gaochunzheng
 *
 */
class DestDistrictNavService extends DataServiceBase {

    const TABLE_NAME = 'dest_district_nav';//对应数据库表
    const PV_REAL = 2;
    const LIKE_INIT = 3;
    const EXPIRE_TIME = 86400;

    /**
     * 添加目的地与行政区数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新目的地与行政区数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 更新目的地与行政区数据
     * @param $dest_id 目的地ID
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function updateByDestId($dest_id, $data) {
        $whereCondition = 'dest_id = ' . $dest_id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据dest_id获取一条目的地数据
     * @param $dest_id 目的地ID
     * @return bool|mixed
     */
    public function getDestDistrictByDestId($dest_id){
        $where_condition=array('dest_id' => "=" . $dest_id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取目的地总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getDestDistrictTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    public function getRsBySql($sql,$one = false){
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $one ? $result->fetch() : $result->fetchAll();
    }

    /**
     * @purpose 根据类型获取数据
     * @param $type 类型
     * @return bool|mixed
     */
    public function geDestDistrictNavList($type){
        $result = array();

        switch ($type) {
        	case 'abroad'://出境
        		$condition = 'is_abroad = 1';
        		break;
        	case 'homepage'://首页
        		$condition = 'is_homepage = 1';
        		break;
        	case 'ticket'://门票
        		$condition = 'is_ticket = 1';
        		break;
        	default:
        		$condition = '';
        		break;
        }

        $result = $this->getList($condition, self::TABLE_NAME);

        return $result;
    }
}