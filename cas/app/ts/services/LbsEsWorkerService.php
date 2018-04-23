<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Components\ApiClient;

class LbsEsWorkerService implements DaemonServiceInterface {

    /**
     * @var EsDataService
     */
    private $datasvc;

    /**
     * @var BeanstalkAdapter
     */
    private $beanstalk;

    private $host;

    private $port;

    private $client;

    private $pageSize = 200;

    private $lbs_index = "cn_city_location";

    //存储导入日志的索引名
    private $import_log = 'lbs_import_log';

    //日志type
    private $log_type = 'import_db_data';

    public function __construct($di) {
        $this->dest_svc = $di->get('cas')->get('destination-data-service');
        $this->vst_dest_sign = $di->get('cas')->get('dist_sign_service');
        $this->vst_dest = $di->get('cas')->get('destin_base_service');

        $this->dest_svc->setReconnect(true);
        $this->vst_dest_sign->setReconnect(true);

        $this->beanstalk = $di->get('cas')->getBeanstalk();
        $es = $di->get('config')->get('elasticsearch');
        $this->host = $es->host;
        $this->port = $es->port;
        $this->client = new ApiClient('http://'.$this->host.':'.$this->port);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        //关闭时收尾任务
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $this->startExec($this->lbs_index);
    }

    /**
     * 获取与需要导入的索引名相似的索引
     * @param $index_name 索引名称
     * @return array
     */
    private function getIndexs($index_name = ''){
        $tmp = array();
        $index_names = array();
        if(!$index_name) return $index_names;
        $res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_cat/indices/'.$index_name.'*');
        foreach(explode("\n",$res) as $v){
            if(trim($v)){
                $tmp = explode(' ',$v);
                $index_names[] = $tmp[2];
            }
        }
        return $index_names;
    }

    /**
     * 创建索引和映射信息
     * @param $index_name 索引名称
     * @param $mappings 映射配置
     * @return bool
     */
    private function createIndex($index_name = '',$mappings  = ''){
        if(!$index_name) return false;
        $res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name,$mappings,array(),'POST');
        return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
    }

    /**
     * 获取索引的映射
     * @param $index_name 索引名称
     * @return string
     */
    private function getMappings($index_name = ''){
        if(!$index_name) return array();
        $res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name.'/_mappings');
        return $res;
    }

    /**
     * 抛出异常
     */
    private function printException($data = array()){
        $data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
        $this->writeLog($data);
        throw new \Exception($data['message']);
    }

    /**
     * 日志写入
     */
    private function writeLog($data = array()){
        $data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['dbname']		= isset($data['dbname']) ? $data['dbname'] : 'null';
        $data['table']		= isset($data['table']) ? $data['table'] : 'null';
        $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->import_log.'/'.$this->log_type,json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),array(),'POST');
    }

    /**
     * 给指定索引创建别名
     * @param string $index_name 索引名称
     * @param array $alias 别名数组
     * @return bool
     */
    private function addAliases($index_name = '',$alias = array()){
        if(!$index_name || !$alias || !is_array($alias)) return false;
        $tmp = array();
        foreach($alias as $alias_name){
            $tmp[] = '{"add": {"index": "'.$index_name.'","alias": "'.$alias_name.'"}}';
        }

        $res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_aliases','{"actions":['.implode(',',$tmp).']}',array(),'POST');
        return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
    }

    /**
     * 删除指定索引
     * @param $index_name 索引名称
     * @return bool
     */
    private function deleteIndex($index_name = ''){
        if(!$index_name) return false;
        $res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name,array(),array(),'DELETE');
        return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
    }

    private function startExec($index_name = ''){
        //先看看存储日志的索引是否存在
        $log_index = $this->getIndexs($this->import_log);
        //没有存储日志的索引,创建它
        if(!$log_index){
            $log_mappings = array(
                'mappings' =>array(
                    $this->log_type => array(
                        'properties' => array(
                            'createtime'	=> array('type' => 'date','format'	=> 'yyyy-MM-dd HH:mm:ss'),
                            'dbname'		=> array('type' => 'string','index' => 'not_analyzed'),
                            'table'			=> array('type' => 'string','index' => 'not_analyzed'),
                            'message'		=> array('type' => 'string')
                        )
                    )
                )
            );
            //创建日志索引
            if(!$this->createIndex($this->import_log,json_encode($log_mappings,JSON_FORCE_OBJECT))){
                $this->printException(array('message' => 'Log Index ['.$this->import_log.'] create fail.'));
            }
        }
        //看看目前存在的索引名
        $index_names	= $this->getIndexs($index_name);

        //如果返回的索引名不为数组或为空数组,抛出异常,停止执行
        if(!is_array($index_names) || !$index_names){
            $this->printException(array('message' => 'online not found ['.$index_name.'] same index.','dbname' => $index_name));
        }
        $oldIndex		= $index_names[0];
        $newIndex		= $index_name.date('Ymd');
        if($oldIndex == $newIndex){
            $this->printException(array('message' => 'online old index and new index name same.','dbname' => $index_name));
        }
        //获取映射
        $_mappings		= $this->getMappings($oldIndex);
        $mapping		= $_mappings[$oldIndex];
        $status			= true;
        //给非$indx_name创建别名
        foreach($index_names as $v){
            //保留一个创建别名,其他的都删除
            if($v == $oldIndex){
                $this->addAliases($v,array($index_name));
            }else{
                $this->deleteIndex($v);
            }
        }
        //创建新索引
        if(!$this->createIndex($newIndex,json_encode($mapping,JSON_FORCE_OBJECT))){
            $this->printException(array('message' => 'new Index ['.$newIndex.'] create fail.','dbname' => $index_name));
        }
        //开始导数据
        $method_name = 'import_'.$index_name;
        $this->$method_name($newIndex);
        //在新索引中添加别名为需要的索引名
        if(!$this->addAliases($newIndex,array($index_name))){
            $this->printException(array('message' => 'new Index ['.$newIndex.'] set Alias fail.','dbname' => $index_name));
        }
        //删除老索引
        if(!$this->deleteIndex($oldIndex)){
            $this->printException(array('message' => 'old Index ['.$oldIndex.'] delete fail.','dbname' => $index_name));
        }
    }

    private function import_cn_city_location($newIndex = ''){
        if(!$newIndex){
            $this->printException(array('message' => 'new Index must have.','dbname' => 'lmm_vst_destination'));
        }
        //--------------1.地理位置信息---------------//
        $table_name = 'biz_district_sign';
        $tmp_count	= $this->vst_dest_sign->getRsBySql("SELECT COUNT(sign_id) AS c FROM {$table_name} WHERE `cancel_flag` = 'Y' AND `sign_type` != 2002 AND `longitude` != 0 AND `latitude` != 0",true);
        $count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
        $totalPage	= ceil($count / $this->pageSize);

        $this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => 'lbs_biz_district_sign','table' => $table_name));

        //逐页存入ES
        for($p = 1;$p <= $totalPage;$p++){
            $list = array();
            $start = ($p - 1) * $this->pageSize;
            $sql = "SELECT sign_id,district_id,sign_type,sign_name,longitude,latitude  FROM {$table_name} WHERE  `cancel_flag` = 'Y' AND `sign_type` != 2002 AND `longitude` != 0 AND `latitude` != 0 LIMIT {$start} , {$this->pageSize}";
            $out = $this->vst_dest_sign->getRsBySql($sql);
            foreach($out as $k => $val){
                $data['id'] = $val['sign_id'];
                $data['dest_name'] = $val['sign_name'];
                $data['district_id'] = $val['district_id'];
                $data['state'] = 'sign';
                $data['dest_type'] = $val['sign_type'];
                $data['location'] = array('lat' => $val['latitude'], 'lon' => $val['longitude']);
                $list[] = '{ "index" : { "_index" : "'.$newIndex.'", "_type" : "city" } }'."\n".json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            }
            $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$newIndex.'/city/_bulk',implode("\n",$list)."\n",array(),'POST');
        }

        //--------------2.景点景区信息---------------//
        $table_name_a = 'biz_dest';
        $table_name_b = 'biz_com_coordinate';
        $tmp_count	= $this->vst_dest->getRsBySql("SELECT count(a.dest_id) as c FROM {$table_name_a} a LEFT JOIN {$table_name_b} b ON a.dest_id = b.object_id WHERE a.cancel_flag = 'Y' AND a.dest_type IN ('VIEWSPOT','SCENIC') AND b.object_type = 'BIZ_DEST' AND b.coord_type = 'BAIDU' AND b.longitude != 0 AND b.latitude != 0",true);
        $count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
        $totalPage	= ceil($count / $this->pageSize);

        $this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => 'lbs_biz_dest_coord','table' => $table_name_a.'_'.$table_name_b));

        //逐页存入ES
        for($p = 1;$p <= $totalPage;$p++){
            $list = array();
            $start = ($p - 1) * $this->pageSize;
            $sql = "SELECT a.dest_id, a.district_id, a.dest_type, a.dest_name, b.longitude, b.latitude FROM {$table_name_a} a LEFT JOIN {$table_name_b} b ON a.dest_id = b.object_id WHERE a.cancel_flag = 'Y' AND a.dest_type IN ('VIEWSPOT','SCENIC') AND b.object_type = 'BIZ_DEST' AND b.coord_type = 'BAIDU' AND b.longitude != 0 AND b.latitude != 0 LIMIT {$start} , {$this->pageSize}";
            $out = $this->vst_dest->getRsBySql($sql);
            foreach($out as $k => $val){
                $data['id'] = $val['dest_id'];
                $data['dest_name'] = $val['dest_name'];
                $data['district_id'] = $val['district_id'];
                $data['state'] = 'dest';
                $data['dest_type'] = $val['dest_type'];
                $data['location'] = array('lat' => $val['latitude'], 'lon' => $val['longitude']);
                $list[] = '{ "index" : { "_index" : "'.$newIndex.'", "_type" : "city" } }'."\n".json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            }
            $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$newIndex.'/city/_bulk',implode("\n",$list)."\n",array(),'POST');
        }
    }
}