<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 产品多出发地 服务类
 * xnw
 */
class ProductPoolStartdistrictAddtionalService extends DataServiceBase {

    const TABLE_NAME = 'pp_startdistrict_addtional';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新
     * @param $where 条件
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($data,$where) {
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);
    }

    /**
     * 删除
     * @param $where 条件
     * @return bool|mixed
     */
    public function delete($where) {
        return $this->getAdapter()->delete(self::TABLE_NAME, $where);
    }

    /**
     * @purpose 根据条件获取
     * @param $where 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getDataList($where, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取总数
     * @param $where 查询条件
     * @return array|mixed
     */
    public function getTotal($where){
        $data=$this->getTotalBy($where, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条
     * @param $where 查询条件
     * @return bool|mixed
     */
    public function getDataOne($where){
        $data=$this->getOne($where, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据出发地ID和产品ids 取多条数据
     * @param $pid 产品ids
     * @param $did 出发地id
     * @return bool|mixed
     */
    public function getListByPidDid($did,$pid){
        $where = " `product_id` IN (".$pid.") AND `start_district_id`=".$did;
        $list =array();
        $data=$this->getList($where, self::TABLE_NAME);
        foreach($data as $item){
            $category_id = $this->query("SELECT `category_id` FROM `pp_product` WHERE product_id =".$item['PRODUCT_ID']);
            if(!empty($category_id['category_id'])){
                $typeF = UCommon::productIdMap($category_id['category_id']);
                $productId = str_pad($typeF,3,'0',STR_PAD_LEFT).str_pad($item['PRODUCT_ID'],10,'0',STR_PAD_LEFT);
                //出发地ID
                $list[$productId]['district_id'] = $did;
                //价格
                $list[$productId]['product_price'] = $item['LOWEST_SALED_PRICE']/100;
                //构建url
                $url = UCommon::getDoMainUrl($category_id['category_id']);
                $list[$productId]['product_url'] = $url.$item['PRODUCT_ID'].'-D'.$did;
            }
        }

        return $list?$list:false;
    }
}