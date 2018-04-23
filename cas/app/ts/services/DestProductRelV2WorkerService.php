<?php 

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
* 产品与目的地关系
*/
class DestProductRelV2WorkerService implements DaemonServiceInterface
{
	/*
     * 基础库
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $distin_base;
	
	/**
	 * @var \Lvmama\Cas\Service\DestinBaseMultiRelationDataService
	 */
	private $biz_dest_multi_relation;

	/**
	 * @var \Lvmama\Cas\Service\DestinBaseMultiRelationDataService
	 */
	private $dest_product_rel_v2;
	
	function __construct($di)
	{
		$this->distin_base = $di->get('cas')->get('destin_base_service');

		$this->biz_dest_multi_relation = $di->get('cas')->get('destin_multi_relation_base_service');

		$this->dest_product_rel_v2 = $di->get('cas')->get('dest_product_rel_v2_service');
	}

	public function process($timestamp = null, $flag = null, $data = array())
	{
		if(!empty($data)){
			$data = json_decode($data, true);
			foreach($data as $item){
				$insert_data = array();
				if(empty($item['category_id'])){
					return json_encode($item);
				}
				$insert_data['product_id'] = $item['product_id'];
				$insert_data['category_id'] = $item['category_id'];
				$insert_data['sub_category_id'] = $item['sub_category_id'];
				$insert_data['create_time'] = $insert_data['update_time'] = date('Y-m-d H:i:s');

				$dest_ids = array_unique(explode(',', $item['dest_id']));
				$condition = array('dest_id' => ' in ("'. implode('","', $dest_ids) . '")');
				$dest_multi = $this->biz_dest_multi_relation->getDefaultList($condition);
				$count = count($dest_multi);
				$diff_res = array();
				if($count > 1){
					for($i = 0; $i < $count - 1; $i++){
						$temp = $this->arrayDiff($dest_multi[$i], $dest_multi[$i + 1]);
						if(!empty($temp)){
							foreach($temp as $res){
								$diff_res[] = $res;
							}
						}
					}
					$diff_res = $this->arrayUnique($diff_res);
				}else{
					$diff_res = $dest_multi;
				}
				//var_dump(json_encode($diff_res));
				if(!empty($diff_res)){
					foreach($diff_res as $diff){
						$insert = $insert_data;
						$insert['continent_id'] = $diff['continent_id'];
						$insert['country_id'] = $diff['country_id'];
						$insert['province_id'] = $diff['province_id'];
						$insert['city_id'] = $diff['city_id'];
						$insert['county_id'] = $diff['county_id'];
						$insert['town_id'] = $diff['town_id'];
						$insert['span_country_id'] = $diff['span_country_id'];
						$insert['span_province_id'] = $diff['span_province_id'];
						$insert['span_city_id'] = $diff['span_city_id'];
						$insert['poi_id'] = 0;
						$type = array('HOTEL', 'VIEWSPOT', 'RESTAURANT', 'SCENIC', 'SCENIC_ENTERTAINMENT', 'SHOP');
						if(in_array($diff['district_type'], $type)){
							$insert['poi_id'] = $diff['dest_id'];
						}
						
						$this->dest_product_rel_v2->insert($insert);
					}
				}
			}
			var_dump(count($data));
		}

		//return;
	}

	private function arrayDiff($arr1, $arr2)
	{
		$keys = array('continent_id', 'country_id', 'province_id', 'city_id', 'county_id', 'town_id', 'span_city_id', 'span_country_id', 'span_province_id');
		$diff = array_diff_assoc($arr1, $arr2);

		foreach($keys as $item){
			if(array_key_exists($item, $diff)){
				if(empty($diff[$item])){
					return array($arr2);
				}elseif(!empty($arr1[$item]) && !empty($arr2[$item])){
					return array($arr1, $arr2);
				}else{
					return array($arr1);
				}
			}
		}

		return array();
	}

	private function arrayUnique($array)
	{
		foreach ($array as $k => $v) {
			foreach ($array as $k2 => $v2) {
				if (($v2 == $v) && ($k != $k2)) {
					unset($array[$k]);
				}
			}
		}
		return $array;
	}

	/**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    private function stopFlag()
    {
        exit('程序跑完了，回家吃饭!');
    }
}