<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Components\ApiClient;
/**
 * 消息 Worker服务类
 *
 * @author win.sx
 *
 */
class AliyunSyncWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var Lvmama\Cas\Service\BaiKeDataService
	 */
	private $baike_svc;
	
	/**
	 * @var Lvmama\Cas\Service\SeoCrawlerUrlDataService
	 */
	private $seo_crawler_svc;
	
	private $soap;

	private $pageSize = 600;

	public function __construct($di) {
		$this->baike_svc = $di->get('cas')->get('baike-data-service');
		$this->seo_crawler_svc = $di->get('cas')->get('seo_crawler_url_service');
		$this->baike_svc->setReconnect(true);
		$this->seo_crawler_svc->setReconnect(true);
		$this->soap = $di->get('soapAliyun');
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		switch($flag){
			case 'baike'://百科数据
				$this->BaiKeProductBase();
				$this->BaiKeProductContent();
				$this->BaiKeSegment();
				$this->BaiKeProductSegment();
				$this->BaiKeSegmentContent();
				break;
			case 'seo'://度假内链数据
				$this->SeoCategory();
				$this->SeoManualUrl();
				$this->SeoCrawlerUrl();
				$this->SeoKeywordUrl();
				$this->SeoKeywordUrlRelated();
				$this->SeoManualCrawler();

				break;
		}
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
	// lmm_baike.productbase
	private function BaiKeProductBase(){
		$this->CommonSync(array(
			'table_name' => 'productbase',
			'primary_key' => 'productId',
			'fields' => array('provinceName','cityName','productName','baiKeName','baiKeUrl','createtime'),
			'service' => $this->baike_svc
		));
	}
	// lmm_baike.productcontent
	private function BaiKeProductContent(){
		$this->CommonSync(array(
			'table_name' => 'productcontent',
			'primary_key' => 'productId',
			'pageSize' => 1,
			'fields' => array('baiKeUrl','content','createtime'),
			'service' => $this->baike_svc
		));
	}
	//lmm_baike.segment
	private function BaiKeSegment(){
		$this->CommonSync(array(
			'table_name' => 'segment',
			'primary_key' => 'id',
			'fields' => array('segment_name','createtime'),
			'service' => $this->baike_svc
		));
	}
	//lmm_baike.productsegment
	private function BaiKeProductSegment(){
		$this->CommonSync(array(
			'table_name' => 'productsegment',
			'primary_key' => 'id',
			'fields' => array('productId','segmentId','createtime'),
			'service' => $this->baike_svc
		));
	}
	//lmm_baike.segmentcontent
	private function BaiKeSegmentContent(){
		$this->CommonSync(array(
			'table_name' => 'segmentcontent',
			'primary_key' => 'pid',
			'fields' => array('content','createtime'),
			'service' => $this->baike_svc
		));
	}
	// lmm_seo.seo_category
	private function SeoCategory(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_category',
			'primary_key' => 'id',
			'fields' => array('category','url','parent_id','update_time'),
			'service' => $this->seo_crawler_svc
		));
	}
	// lmm_seo.seo_crawler_url
	private function SeoCrawlerUrl(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_crawler_url',
			'primary_key' => 'id',
			'fields' => array('title','url','channel_id','create_time'),
			'service' => $this->seo_crawler_svc
		));
	}
	// lmm_seo.seo_keyword_url
	private function SeoKeywordUrl(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_keyword_url',
			'primary_key' => 'id',
			'fields' => array('keyword_id','keyword_title','keyword_url','channel_id','url_id','url','display_limit','rule'),
			'service' => $this->seo_crawler_svc
		));
	}
	// lmm_seo.seo_keyword_url_related
	private function SeoKeywordUrlRelated(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_keyword_url_related',
			'primary_key' => 'id',
			'fields' => array('url_id','url','keyword_id','channel_id','related_id','related_title','relation_url','display_limit','rule'),
			'service' => $this->seo_crawler_svc
		));
	}
	// lmm_seo.seo_manual_crawler
	private function SeoManualCrawler(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_manual_crawler',
			'primary_key' => 'id',
			'fields' => array('manual_url_id','crawler_url_id'),
			'service' => $this->seo_crawler_svc
		));
	}
	// lmm_seo.seo_manual_url
	private function SeoManualUrl(){
		$this->CommonSync(array(
			'dbname' => 'lmm_seo',
			'table_name' => 'seo_manual_url',
			'primary_key' => 'id',
			'fields' => array('category_id','channel_id','url','keyword','max_match_times','status','crawl_status','create_time','update_time'),
			'service' => $this->seo_crawler_svc
		));
	}

	/**
	 * 通用同步处理程序
	 * @param array $data
	 */
	private function CommonSync(array $data){
		if(!isset($data['table_name'])) die('please input param table_name!');
		if(!isset($data['primary_key'])) die('please input param primary_key!');
		if(!isset($data['fields']) || !count($data['fields'])) die('please input param fields!');
		if(!isset($data['service'])) die('please input param service');
		$dbname = isset($data['dbname']) ? $data['dbname'] : 'lmm_baike';
		$pageSize = empty($data['pageSize']) ? $this->pageSize : $data['pageSize'];
		$table_name = $data['table_name'];
		$fields = $data['fields'];
		$primary_key = $data['primary_key'];
		//获取总条数以便分页
		$sql = 'SELECT COUNT('.$primary_key.') AS c FROM '.$dbname.'.'.$table_name;
		$tmp = $this->getSoapBySql($sql);
		$total = isset($tmp[0]['c']) ? $tmp[0]['c'] : 0;
		$totalPage = ceil($total / $pageSize);
		for($i = 1;$i <= $totalPage;$i++){
			$params = array();
			$start = ($i - 1) * $pageSize;
			$sql = 'SELECT '.$primary_key.','.implode(',',$fields).' FROM '.$dbname.'.'.$table_name.' LIMIT '.$start.','.$pageSize;
			echo '['.date('Y-m-d H:i:s').']'.$sql."\n";
			$rs = $this->getSoapBySql($sql);
			$keys = array();
			foreach($rs as $row){
				$tmp = array();
				foreach($row as $k => $v){
					$tmp[':'.$k] = $v;
				}
				$params[] = $tmp;
			}
			foreach($fields as $field){
				$keys[] = '`'.$field.'` = VALUES(`'.$field.'`)';
			}
			$execute_sql = 'INSERT INTO '.$table_name.'(`'.$primary_key.'`,`'.implode('`,`',$fields).'`) VALUES (:'.$primary_key.',:'.implode(',:',$fields).') ON DUPLICATE KEY UPDATE '.implode(',',$keys);
			$data['service']->execute($execute_sql,$params,true);
		}
	}
	/**
	 * 查询阿里云机器上的数据并json_decode
	 * @param $sql
	 * @param array $params
	 * @return mixed
	 */
	private function getSoapBySql($sql,$params = array()){
		$rs = $this->soap->query($sql,$params);
		return json_decode($rs,true);
	}
}
