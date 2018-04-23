<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 门票产品对应的POI及其城市级目的地关系
 *
 * @author win.sx
 *
 */
class SourceProductRelDataService extends DataServiceBase {

    const TABLE_NAME = 'dest_product_rel';//对应数据库表
    const PRIMARY_KEY = 'productId'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;
	
	private $fields = array('productId','dest_id','poi_id','categoryId');
	
	public function get($product_id,$poi_id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE productId = ' . $product_id.' AND poi_id = '.$poi_id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
    /**
     * 
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }
	/**
	 * 保存产品与POI及目的地的数据
	 * @param $data 需要保存的产品与POI
	 * @return bool|mixed
	 */
    public function save($data, $table_name = ''){
		foreach($data as $k => $v){
			if(!in_array($k,$this->fields)){
				unset($data[$k]);
			}
		}
		$rs = $this->get($data['productId'],$data['poi_id']);
		if(!$rs){
			//先查出poi对应的城市级或者以上级别的目的地
			$destination = $this->di->get('cas')->get('destination-data-service');
			$poi_detail = $destination->getDestById($data['poi_id']);
			$data['poi_name'] = isset($poi_detail['dest_name']) ? $poi_detail['dest_name'] : '';
			$dest = $destination->getParentDest($data['poi_id'],array('CITY','SPAN_CITY','PROVINCE','SPAN_PROVINCE','COUNTRY','SPAN_COUNTRY','CONTINENT'));
			if($dest){
				$data['dest_id'] = $dest['dest_id'];
				$dest_detail = $destination->getDestById($data['dest_id']);
				$data['dest_name'] = isset($dest_detail['dest_name']) ? $dest_detail['dest_name'] : '';
			}
			if(!isset($data['createtime'])){
				$data['createtime'] = date('Y-m-d H:i:s');
			}
			if(!isset($data['updatetime'])){
				$data['updatetime'] = date('Y-m-d H:i:s');
			}
			return $this->insert($data);
		}
	}
	public function batchSave($product_id,$poi_ids,$category_id){
		if(empty($product_id)) return false;
		try{
			$destination = $this->di->get('cas')->get('destination-data-service');
			$this->beginTransaction();
			$this->deleteFrom('productId = '.$product_id,self::TABLE_NAME);
			foreach($poi_ids as $poi_id){
				$data = array(':productId' => $product_id,':categoryId' => $category_id);
				//先查出poi对应的城市级或者以上级别的目的地
				$poi_detail = $destination->getDestById($poi_id);
				$data[':poi_id'] = $poi_id;
				$data[':poi_name'] = isset($poi_detail['dest_name']) ? $poi_detail['dest_name'] : '';
				$data[':dest_id'] = $poi_id;
				$data[':dest_name'] = '';
				$dest = $destination->getParentDest(
					$poi_id,
					array('CITY','SPAN_CITY','PROVINCE','SPAN_PROVINCE','COUNTRY','SPAN_COUNTRY','CONTINENT')
				);
				if($dest){
					$data[':dest_id'] = $dest['dest_id'];
					$dest_detail = $destination->getDestById($dest['dest_id']);
					$data[':dest_name'] = isset($dest_detail['dest_name']) ? $dest_detail['dest_name'] : '';
				}
				$data[':createtime'] = $data[':updatetime'] = date('Y-m-d H:i:s');
				$this->execute(
					'INSERT INTO '.self::TABLE_NAME.'(`productId`,`dest_id`,`poi_id`,`categoryId`,`dest_name`,`poi_name`,`createtime`,`updatetime`) VALUES(:productId,:dest_id,:poi_id,:categoryId,:dest_name,:poi_name,:createtime,:updatetime)',
					$data
				);
			}
			$this->commit();
			return true;
		}catch (\PDOException $e){
			$this->rollBack();
			var_dump($e);
			return false;
		}
	}

	public function destProductRelSave($data)
	{
		// $data = json_decode($data);
		$dest_ids = explode(",", $data['dest_id']);
		$insert_data = array('productId' => $data['product_id'], 'categoryId' => $data['categroy_id']);

		//根据poi_id获取dest_id
		$destin_multi_relation = $this->di->get('cas')->get('destin_multi_relation_base_service');
		$count = count($dest_ids);
		if($count > 1){
			foreach($dest_ids as $item){
				$insert_data['poi_id'] = $item;
				$res = $destin_multi_relation->getOneById($item);

				if(!empty($res['city_id'])){
					$insert_data['dest_id'] = $res['city_id'];
				}else{
					$insert_data['dest_id'] = $insert_data['poi_id'];
				}
				//判断记录是否已经存在
				$dest_product_rel = $this->getDestProduct($insert_data['productId'], $insert_data['poi_id'], $insert_data['categoryId']);
				if(empty($dest_product_rel)){
					$destin_base = $this->di->get('cas')->get('destin_base_service');

					$dest_list = $destin_base->geDestNameByIds(array($insert_data['dest_id'], $insert_data['poi_id']));

					if(!empty($dest_list)){
						$insert_data['dest_name'] = $dest_list[$insert_data['dest_id']];
						$insert_data['poi_name'] = $dest_list[$insert_data['poi_id']];
						$insert_data['createtime'] = $insert_data['updatetime'] = date('Y-m-d H:i:s');

						return $this->insert($insert_data);
					}
				}else{
					foreach($dest_product_rel as $dest_product){
						if($dest_product['productId'] == $insert_data['productId'] && ($dest_product['dest_id'] != $insert_data['dest_id'] || $dest_product['poi_id'] != $insert_data['poi_id'] || $dest_product['categoryId'] != $insert_data['categoryId'])){
							$this->deleteDestProductById($dest_product['id']);
						}
					}
				}
			}
		}else{
			$insert_data['poi_id'] = $data['dest_id'];
			if(!empty($data['dest_id'])){
				$res = $destin_multi_relation->getOneById($data['dest_id']);

				if(!empty($res['city_id'])){
					$insert_data['dest_id'] = $res['city_id'];
				}else{
					$insert_data['dest_id'] = $insert_data['poi_id'];
				}
				//判断记录是否已经存在
				$dest_product_rel = $this->getDestProduct($insert_data['productId'], $insert_data['poi_id'], $insert_data['categoryId']);
				if(empty($dest_product_rel)){
					$destin_base = $this->di->get('cas')->get('destin_base_service');
					$dest_list = $destin_base->geDestNameByIds(array($insert_data['dest_id'], $insert_data['poi_id']));

					if(!empty($dest_list)){
						$insert_data['dest_name'] = $dest_list[$insert_data['dest_id']];
						$insert_data['poi_name'] = $dest_list[$insert_data['poi_id']];
						$insert_data['createtime'] = $insert_data['updatetime'] = date('Y-m-d H:i:s');

						return $this->insert($insert_data);
					}
				}else{
					foreach($dest_product_rel as $dest_product){
						if($dest_product['productId'] == $insert_data['productId'] && ($dest_product['dest_id'] != $insert_data['dest_id'] || $dest_product['poi_id'] != $insert_data['poi_id'] || $dest_product['categoryId'] != $insert_data['categoryId'])){
							$this->deleteDestProductById($dest_product['id']);
						}
					}
				}
			}
		}
		return;
	}

	public function getDestProduct($product_id, $poi_id, $categroy_id)
	{
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE productId = ' . $product_id . ' AND poi_id = ' . $poi_id . ' AND categoryId = ' . $categroy_id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);

		return $result->fetchAll();
	}

	public function deleteDestProductById($id)
	{
		$condition = 'id = ' .$id;
		return $this->getAdapter()->delete(self::TABLE_NAME, $condition);
	}
}