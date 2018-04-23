<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 预算数据 服务类
 *
 * @author
 *
 */
class SemBudgetDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_budget';//对应数据库表



    /**
     * 上传预算数据
     * @param $excelData
     * @return bool|mixed
     */
    public function saveBudget($excelData){
        return $this->save($excelData,self::TABLE_NAME );
    }

    /**
     * @return array
     */
    public function getChargeDepart(){
        $sql = "select chargeDepart from ".self::TABLE_NAME." group by chargeDepart";
        $rs = $this->getAdapter()->query($sql);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $tmp = array();
        $raw = $rs->fetchAll();
        foreach($raw as $v){
            $tmp[] = $v['chargeDepart'];
        }
        return $tmp;
    }
    /**
     * @purpose 根据条件获取预算
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getBudgetList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }
    /**
     * @purpose 根据条件获取预算条总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getBudgetTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据sql语句直接执行并返回结果
     * @param $sql
     * @param bool $one
     * @return array
     */
    public function getRsBySql($sql,$one = false){
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $one ? $result->fetch() : $result->fetchAll();
    }
}