<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Components\ApiClient;

/**
 * 扫码购报告信息 服务类
 *
 * @author flash.guo
 *
 */
class ScanbuyReportEsDataService extends DataServiceBase {

	protected $baseUri = 'http://172.20.4.178:9200/';
	private $ttl = 1800;
	
	public function __construct($di, $redis = null, $beanstalk = null)
	{
		$this->di = $di;
		$this->redis = $redis;
		$this->beanstalk = $beanstalk;
		if ($di->get('config')->elasticsearch->toArray()) {
			$this->baseUri = 'http://' . $di->get('config')->elasticsearch->toArray()['host'] . ':' . $di->get('config')->elasticsearch->toArray()['port'];
		}
		$this->client = new ApiClient($this->baseUri);
	}

    /**
     * @purpose 根据条件获取扫码购报告信息
     * @param $terms 查询条件
     * @param $range 查询范围
     * @param $size 查询条数
     * @param $group 查询分组
     * @param $sort 查询排序
     * @param $interval 时间分段
     * @return array|mixed
     */
    public function getReportList($terms, $range, $limit, $group, $sort, $interval){
    	$must = $must_not = array();
    	foreach ($terms as $key => $term) {
    		if (!is_array($term) && strstr($term, "!=")) {
    			$must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
    		} else {
    			$must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
    		}
    	}
    	//筛选条件
		$query = array(
			"query" => array(
				"filtered" => array(
					"query"=> array(
						"bool" => array("must" => $must, "must_not" => $must_not)
    				),
					"filter"=> empty($range) ? array() : array("range" => $range)
				)
			)
		);
		if ($group) {
			$query["size"] = 0;
			//统计聚合数据
			$query["aggs"] = array(
				"group_".$group => array(
					"aggs" => array(
						"orderNum" => array(
							"sum" => array("field" => "orderNum")
						),
						"actualAmount" => array(
							"sum" => array("field" => "actualAmount")
						),
						"quantity" => array(
							"sum" => array("field" => "quantity")
						)
					)
				)
			);
			if ($group == 'date_histogram') {//按天分组
				$query["aggs"]["group_".$group]["date_histogram"] = array(
					"field" => "paymentTime",
					"format" => "yyyy-MM-dd",
					"time_zone" => "+08:00",
        			"interval" => empty($interval) ? "hour" : $interval,
				);
				//获取对orderId分组后订单数据
				$query["aggs"]["group_".$group]["aggs"]["group_orderId"] = array(
	          		"aggs" => array(
			      		"orderNum" => array(
			          		"min" => array("field" => "orderNum")
						),
			      		"actualAmount" => array(
			          		"min" => array("field" => "actualAmount")
						)
			      	),
	          		"terms" => array(
						"field" => "orderId",
						"order" => array("_term" => "asc"),
						"size" => 0
			      	)
			    );
				//获取对userId分组后用户数据
				$query["aggs"]["group_".$group]["aggs"]["group_userId"] = array(
	          		"aggs" => array(
			      		"userNum" => array(
			          		"min" => array("field" => "orderNum")
						)
			      	),
	          		"terms" => array(
						"field" => "userId",
						"order" => array("_term" => "asc"),
						"size" => 0
			      	)
			    );
				$query["aggs"]["group_".$group]["aggs"]["orderNum"] = array(
					"sum_bucket" => array("buckets_path" => "group_orderId>orderNum")
			    );
				$query["aggs"]["group_".$group]["aggs"]["actualAmount"] = array(
					"sum_bucket" => array("buckets_path" => "group_orderId>actualAmount")
			    );
				$query["aggs"]["group_".$group]["aggs"]["userNum"] = array(
					"sum_bucket" => array("buckets_path" => "group_userId>userNum")
			    );
				$query["aggs"]["group_".$group]["aggs"]["quantity"] = array(
					"sum" => array("field" => "quantity")
			    );
			} else {
				$query["aggs"]["group_".$group]["terms"] = array(
					"field" => $group,
					"order" => empty($sort) ? array("_term" => "asc") : $sort,
					"size" => empty($limit['page_size']) ? 10000 : $limit['page_size']
				);
			}
		} else {
			$query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
			$query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num']-1)*$limit['page_size'];
			$query["sort"] = empty($sort) ? array(array("orderId" => "desc")) : $sort;
		}
		$result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
		$query = json_encode($query);
		$res = $this->client->exec('lmm_scanbuy/scanbuy_report/_search?preference=_primary_first', $query, '', 'post');
		if ($group) {
			$itemCount = 0;
			$result = array('list' => array());
			if (isset($res['aggregations']['group_'.$group]['buckets'])) {
				foreach ($res['aggregations']['group_'.$group]['buckets'] as $val) {
					$val['orderNum'] = $val['orderNum']['value'];
					$val['actualAmount'] = $val['actualAmount']['value'];
					$val['quantity'] = $val['quantity']['value'];
					isset($val['userNum']) && $val['userNum'] = $val['userNum']['value'];
					$val[$group] = $group == 'date_histogram' ? date('Y-m-d H:i:s', intval($val['key']/1000)) : $val['key'];
					unset($val['key_as_string']);
					$result['list'][$val[$group]] = $val;
					unset($result['list'][$val[$group]]['key']);
					unset($result['list'][$val[$group]]['group_orderId']);
					unset($result['list'][$val[$group]]['group_userId']);
					$itemCount++;
				}
			}
        	$pageCount = intval(($itemCount-1)/(empty($limit['page_size']) ? 10 : $limit['page_size'])+1);
			$result['pages'] = array(
				'itemCount' => $itemCount,
				'pageCount' => $pageCount,
				'page' => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
				'pageSize' => $query["size"]
			);
		} else {
			if (isset($res['hits'])) {
				$itemCount = intval($res['hits']['total']);
				$itemCount = $itemCount > 10000 ? 10000 : $itemCount;
	        	$pageCount = intval(($itemCount-1)/(empty($limit['page_size']) ? 10 : $limit['page_size'])+1);
				$result['pages'] = array(
					'itemCount' => $itemCount,
					'pageCount' => $pageCount,
					'page' => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
					'pageSize' => $result['pages']["pageSize"]
				);
				foreach ($res['hits']['hits'] as $val) {
					$val['_source']['_id'] = $val['_id'];
					$val['_source']['paymentTime'] = date('Y-m-d H:i:s', intval($val['_source']['paymentTime']/1000));
					$val['_source']['registerDate'] = date('Y-m-d H:i:s', intval($val['_source']['registerDate']/1000));
					$result['list'][] = $val['_source'];
				}
			}
		}
		$res = $val = $query = null;
		return $result;
    }

    /**
     * @purpose 删除一条推广报告信息
     * @param $_id 报告信息索引id
     * @return array|mixed
     */
    public function delReport($_id){
    	if (empty($_id)) return false;
    	$result = $this->client->exec('lmm_scanbuy/scanbuy_report/'.$_id, '', '', 'delete');
    	return $result;
    }
}