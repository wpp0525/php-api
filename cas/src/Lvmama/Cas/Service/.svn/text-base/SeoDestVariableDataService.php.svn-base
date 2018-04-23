<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;

/**
 * 大目的地关键词变量 服务类
 *
 * @author flash.guo
 *
 */
class SeoDestVariableDataService extends DataServiceBase {

    const TABLE_NAME = 'seo_dest_variable';//对应数据库表
    const PRIMARY_KEY = 'variable_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加大目的地关键词变量
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新大目的地关键词变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除大目的地关键词变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取大目的地关键词变量
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getVarList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条大目的地关键词变量
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneVar($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条大目的地关键词变量
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('variable_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据关键词ID获取一条大目的地关键词变量
     * @param $kid 关键词ID
     * @return bool|mixed
     */
    public function getOneByKid($kid){
        if(!$kid) return false;
        $where_condition=array('keyword_id'=>"=".$kid);
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据关键词ID删除一条大目的地关键词变量
     * @param $kid 关键词ID
     * @param $varname 变量名称
     * @return bool|mixed
     */
    public function delVarByKid($kid, $varname = ''){
        if(empty($kid)) return false;
        $where_condition = 'keyword_id ='.$kid;
        if(!empty($varname)) $where_condition .= " AND variable_name = '" . $varname . "'";
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
    /**
     * @purpose 根据关键词ID删除一条大目的地关键词变量
     * @param $kid 关键词ID
     * @return bool|mixed
     */
    public function delAllVarByKid($kid){
      $whereCondition = 'keyword_id = ' . intval($kid);
      return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }
    /**
     * 保存大目的地页面时获取所有出发地的产品&生成坑位
     * @param $template_id 模板ID
     * @param $manualId 页面ID
     * @param $destId 页面绑定的目的地ID
     * @return bool
     */
    public function destSave($template_id,$manualId,$dest_id,$keyword_pinyin){
        try{
            //获取频道和路由
            $channel_route = $this->query('SELECT channel_id,route_id FROM seo_template WHERE template_id = '.$template_id);
            $channel_id = $channel_route['channel_id'];
            $route_id = $channel_route['route_id'];
            $module_ids = array();
            //给线路产品添加多tab和相应产品支持,非大目的地频道不用进行动态存储多个TAB情况
            if($channel_id == 1){
                $dest_srv = $this->di->get('cas')->get('destination-data-service');
                $route_srv = $this->di->get('cas')->get('seo_vst_route_service');
                $createtime = time();
                $variables = $this->query("SELECT * FROM seo_template_variable WHERE template_id = {$template_id} AND group_type = 'product'",'All');
                $filter_param = '';
                $productType = '';
                if(!$variables) return;
                foreach($variables as $k => $v){
                    if(in_array($v['module_id'],$module_ids)) continue;
                    $module_ids[] = $v['module_id'];
                    if(strpos($v['variable_name'],'currSeasonHot') || strpos($v['variable_name'],'hotData')) continue;
                    if(strpos($v['variable_name'],'LuxuriousTrip')) continue;
                    if(strpos($v['variable_name'],'gHotel')){
                        $filter_param = 'filter_theme';
                        $productType = 'HOTEL';
                    }else if(strpos($v['variable_name'],'ticket')){
                        $filter_param = 'filter_theme';
                        $productType = 'TICKET';
                    }else if(strpos($v['variable_name'],'local')){
                        $filter_param = 'filter_days';
                        $productType = 'LOCAL';
                    }else if(strpos($v['variable_name'],'freetourData') || strpos($v['variable_name'],'ziyouxing')){
                        $filter_param = 'filter_station';
                        $productType = 'ZIYOUXING';
                    }else if(strpos($v['variable_name'],'group')){
                        $filter_param = 'filter_station';
                        $productType = 'GROUP';
                    }else if(strpos($v['variable_name'],'romantic') || strpos($v['variable_name'],'scenictour')){
                        $filter_param = 'filter_dest';
                        $productType = 'SCENICTOUR';
                    }else if(strpos($v['variable_name'],'plane') || strpos($v['variable_name'],'freetour')){
                        $filter_param = 'filter_station';
                        $productType = 'FREETOUR';
                    }
                    /*
                    if(strpos($v['variable_name'],'route')){
                        $filter_param = 'filter_station';
                        $productType = 'ROUTE';
                    }*/
                    //非出发地的暂时不添加
                    if(!$filter_param || $filter_param != 'filter_station') continue;
                    $dest = $dest_srv->getDestById($dest_id);
                    $filter_datas = $route_srv->getFilterContent($v['variable_name'],$dest,$filter_param);
                    $tmp = array();
                    foreach($filter_datas as $key => $filter){
                        if($filter['id'] == 0) continue;//去掉不限出发地
                        unset($filter['num']);
                        $tmp[] = $filter;
                    }
                    $filter_datas = $tmp;
                    $num = count($filter_datas);//统计筛选项内容数量
                    $haveNumRs = $this->query("SELECT COUNT(*) AS c FROM seo_template_variable WHERE template_id = {$template_id} AND module_id = {$v['module_id']} AND group_type = '{$v['group_type']}'");
                    $haveNum = $haveNumRs['c'];
                    //统计下模块变量表中产品组的量
                    $moduleVarNumRs = $this->query("SELECT COUNT(*) AS c FROM seo_module_variable WHERE module_id = {$v['module_id']} AND group_id = {$v['group_id']}");
                    $moduleNum = $moduleVarNumRs['c'];
                    $batchInsert = array();
                    $moduleVarBatchInsert = array();
                    $join_str = strpos($v['variable_url'],'?') ? '&' : '?';
                    //添加坑位
                    for($i = $haveNum + 1;$i <= $num;$i++){
                        $variable_name = $v['variable_name'].'_additional'.$i;
                        $variable_desc = $v['variable_desc'].'_additional'.$i;
                        $variable_url = $v['variable_url'].$join_str.'placeholder='.$i;
                        $batchInsert[] = "('{$variable_name}','{$variable_desc}',{$v['group_id']},'{$v['group_type']}',{$v['module_id']},{$v['template_id']},'{$variable_url}',{$v['max_count']},{$createtime})";
                    }
                    //模板变量关系批量插入
                    if($batchInsert){
                        $this->query('INSERT INTO seo_template_variable(`variable_name`,`variable_desc`,`group_id`,`group_type`,`module_id`,`template_id`,`variable_url`,`max_count`,`create_time`) VALUES '.implode(',',$batchInsert));
                    }
                    $tmp = explode('_',$v['variable_name']);
                    for($i = $moduleNum + 1;$i <= $num;$i++){
                        $variable_name = $tmp[2].'_additional'.$i;
                        $variable_desc = $v['variable_desc'].'_additional'.$i;
                        $variable_url = $v['variable_url'].$join_str.'placeholder='.$i;
                        $moduleVarBatchInsert[] = "('{$variable_name}','{$variable_desc}',{$v['module_id']},'{$variable_url}',{$v['max_count']},{$createtime},{$v['group_id']})";
                    }
                    //模块变量批量插入
                    if($moduleVarBatchInsert){
                        $this->query('INSERT INTO seo_module_variable(`variable_name`,`variable_des`,`module_id`,`variable_default`,`max_count`,`create_time`,`group_id`) VALUES '.implode(',',$moduleVarBatchInsert));
                    }
                    $variable_content = array();
                    $variable_filter = array();
                    $pType = strtolower($productType);
                    //tab内容及其筛选项内容添加
                    $destTabInfo = $this->query("SELECT * FROM seo_dest_variable WHERE `keyword_id` = {$manualId} AND `module_id` = {$v['module_id']} AND `group_type` = 'tab'");
                    if(isset($destTabInfo['variable_id']) && $destTabInfo['variable_id']){
                        $destFilterContent = $destTabInfo['variable_content'] ? json_decode($destTabInfo['variable_content'],true)  : array();
                        $destFilterVar = $destTabInfo['variable_filter'] ? json_decode($destTabInfo['variable_filter'],true) : array();
                        $variable_content = $destFilterContent;
                        $variable_filter = $destFilterVar;
                        foreach($filter_datas as $f => $fd){
                            if(!isset($variable_content[$f])){
                                $fd['url'] = UCommon::getProductTypeUrl($pType,array('pinyin' => $keyword_pinyin,'dest_id'=>$dest_id))[0].'-D'.$fd['id'];
                                $variable_content[$f] = $fd;
                            }
                            if(!isset($variable_filter[$f+1])){
                                $variable_filter[$f+1] = array(
                                    $filter_param => $fd['id']
                                );
                            }
                        }
                        $this->update($destTabInfo['variable_id'],array(
                            'variable_content' => json_encode($variable_content,JSON_UNESCAPED_UNICODE),
                            'variable_filter' => json_encode($variable_filter,JSON_UNESCAPED_UNICODE),
                            'update_time' => time()
                        ));
                    }else{
                        //把查看更多的链接加上
                        foreach($filter_datas as $key => $filter){
                            $filter['url'] = UCommon::getProductTypeUrl($pType,array('pinyin' => $keyword_pinyin,'dest_id'=>$dest_id))[0].'-D'.$filter['id'];
                            $variable_content[$key] = $filter;
                            $variable_filter[$key+1] = array($filter_param => $filter['id']);
                        }
                        //获取TAB项的变量名
                        $templateTabInfo = $this->query("SELECT variable_id,variable_name FROM seo_template_variable WHERE template_id = {$v['template_id']} AND module_id = {$v['module_id']} AND group_type = 'tab'");
                        $this->insert(array(
                            'variable_name' => $templateTabInfo['variable_name'],
                            'module_id' => $v['module_id'],
                            'keyword_id' => $manualId,
                            'group_type' => 'tab',
                            'variable_content' => json_encode($variable_content,JSON_UNESCAPED_UNICODE),
                            'max_count' => 0,
                            'variable_filter' => json_encode($variable_filter,JSON_UNESCAPED_UNICODE),
                            'create_time' => time()
                        ));
                    }
                }
                $variables = $this->query("SELECT variable_id,module_id,max_count FROM seo_template_variable WHERE `template_id` = {$template_id} AND `group_type` = 'product' ORDER BY variable_id ASC",'All');
            }
            $coordinate = array();
            $pp = $this->di->get('cas')->get('product_pool_data');
            $channel_route = intval($channel_id)*100000+intval($route_id);
            //把所有可能生成坑位的变量都给A接口生成好
            foreach($variables as $vk=>$var){
                $pp->buildRule($channel_route.'.0.'.$var['variable_id'].'.'.$var['max_count']);
                $coordinate[] = $channel_route.'.'.$manualId.'.'.$var['variable_id'].'.0';
            }
            $pp->buildPlaceByCoordinate($coordinate);
        }catch (\Exception $e){
            var_dump($e);
            return;
        }
    }
}
