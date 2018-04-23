<?php
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Filesystem\Adapters\LocalAdapter;
/**
 * 模板控制器
 *
 * @author flash.guo
 *
 */
class TemplateController extends ControllerBase {
    private $seo_tpl_svc;
    private $seo_tpl_mdl_svc;
    private $seo_mdl_svc;
    private $seo_mdl_var_svc;

	/**
	 * @var Lvmama\Cas\Service\SeoDestVariableDataService
	 */
	private $seo_dest_variable_svc;
	/**
	 * @var Lvmama\Cas\Service\SeoTemplateVariableDataService
	 */
	private $seo_tpl_var_svc;

    public function initialize() {
        parent::initialize();
        $this->seo_tpl_svc = $this->di->get('cas')->get('seo_template_service');
        $this->seo_tpl_var_svc = $this->di->get('cas')->get('seo_template_variable_service');
        $this->seo_tpl_mdl_svc = $this->di->get('cas')->get('seo_template_module_service');
        $this->seo_mdl_svc = $this->di->get('cas')->get('seo_module_service');
        $this->seo_mdl_var_svc = $this->di->get('cas')->get('seo_module_variable_service');
		$this->seo_dest_variable_svc = $this->di->get('cas')->get('seo_dest_variable_service');
    }

	/**
	 * 模板详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $tplname = trim($this->request->get('tplname'));
		$columns = $this->request->get('columns');
		$conditions = array();
        !empty($id) && $conditions['template_id'] = "=" . $id;
        !empty($tplname) && $conditions['template_name'] = "='" . $tplname . "'";
        !empty($conditions) && $tpl_info = $this->seo_tpl_svc->getOneTemplate($conditions,$columns);
        if(empty($tpl_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $tpl_info));
	}

	/**
	 * 模板列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns = trim($this->request->get('columns'));
        $order = trim($this->request->get('order'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order = $order ? $order : null;
        $template_info = $this->seo_tpl_svc->getTemplateList($condition, $limit, $columns, $order);
        if(empty($template_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
        	return;
        }
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $template_info));
        	return;
        }
        $total_records = $this->seo_tpl_svc->getTemplateTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $template_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 模板新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	if(empty($post['template_content'])){
                $post['template_content'] = "内容不能为空";
            }
        	$result = $this->seo_tpl_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模板新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模板更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_tpl_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模板更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模板删除
	 */
	public function deleteAction() {
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
        	$result = $this->seo_tpl_svc->delete($id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模板删除失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	/**
	 * 模版关联模块列表
	 */
	public function moduleListAction() {
        $mid = intval($this->request->get('mid'));
        if ($mid) {
			$condition['tm.module_id'] = "=".$mid;
			$template_mdls = $this->seo_tpl_mdl_svc->getModuleList($condition);
        } else {
	        $id = intval($this->request->get('id'));
			if(empty($id)) {
				$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
				return;
			}
			$condition['tm.template_id'] = "=".$id;
			$template_mdls = $this->seo_tpl_mdl_svc->getModuleList($condition);
        }

		if(empty($template_mdls)) {
			$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
			return;
		}
		$this->jsonResponse(array('results' => $template_mdls));
	}
	/**
	 * 设置模板模块关联
	 */
	public function setModuleAction() {
		$post = $this->request->getPost();
        $tid = intval($post['tid']);
        if(empty($tid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
        	return;
        }
        $mid = intval($post['mid']);
        $operation = empty($post['operation']) ? "" : trim($post['operation']);
        //模块排序
        if ($operation == 'resort') {
          $sort = isset($post['module_sort'])?$post['module_sort']:'';
          $sort = json_decode($sort, true);
          $num = 0;
          foreach($sort as $s){
            $mdl = array();
            $mdl['module_sort'] = $num;
            $mdl['create_time'] = time();
            $this->seo_tpl_mdl_svc->update($s, $mdl);
            $num++;
          }
          return $this->jsonResponse(array('code' => '4000', 'msg' => 'success resort'));
        }
        if ($operation == 'unbind') {
        	$module_info = array();
        	$id = intval($post['id']);
          $condition = array();
          $condition['template_id'] = "=".(isset($post['tid'])?$post['tid']:'');
          $condition['module_id'] = "=".(isset($post['mid'])?$post['mid']:'');
          $template_vars = $this->seo_tpl_var_svc->getVarList($condition);
          if($template_vars){
            foreach($template_vars as $x){
              if(strpos($x['variable_name'], 'id'.$id) !== false)
                $this->seo_tpl_var_svc->delete($x['variable_id']);
            }
          }
	        $result = $this->seo_tpl_mdl_svc->delete($id);
        } else {
        	$condition['module_id'] = "=".$mid;
        	$module_info = $this->seo_mdl_svc->getOneModule($condition);
        	if(empty($module_info)) {
        		$this->_errorResponse(OPERATION_FAILED,'模块不存在');
        		return;
        	}
			$module_info['module_variables'] = $this->seo_mdl_var_svc->getVarList(array('module_id' => "=".$mid));
      //get the grouptype
      if($module_info['module_variables'] && is_array($module_info['module_variables'])){
        $seo_var_group = $this->di->get('cas')->get('seo_variable_group_service');
        foreach($module_info['module_variables'] as $x => $x_val){
          $ginfo =  $seo_var_group->getOneGroup(array(
            'group_id' => '='.$x_val['group_id'],
          ));
          if($ginfo && isset($ginfo['group_type'])){
            $module_info['module_variables'][$x]['group_type'] = $ginfo['group_type'];
          }else{
            $module_info['module_variables'][$x]['group_type'] = '';
          }
        }
      }
			$module_info['module_variables'] = empty($module_info['module_variables']) ? array() : $module_info['module_variables'];
			if ($operation == 'bind') {
        		$mdl = array();
	        	$mdl['template_id'] = $tid;
	        	$mdl['module_id'] = $mid;
	        	$mdl['create_time'] = time();
	        	$result = $this->seo_tpl_mdl_svc->insert($mdl);
			} else {
				//仅刷新模块
        		$id = intval($post['id']);
        		$mdl = array();
	        	$mdl['update_time'] = time();
	        	$result = $this->seo_tpl_mdl_svc->update($id, $mdl);
        		if(!empty($result)) $result = $id;
			}
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置模板模块关联失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result, 'module' => $module_info));
	}
	/**
	 * 获取模板所有变量
	 */
	public function getVarAction() {
        $tid = intval($this->request->get('tid'));
        if (empty($tid) || !is_numeric($tid)) {
	        $this->_errorResponse(DATA_NOT_FOUND,'模板变量不存在');
	        return;
		}
		$module_id = $this->request->get('module_id');
		$tour = $this->request->get('tour');//大目的地前端页面,返回指定变量
		$fields = $tour ? 'variable_id,variable_name,group_type,module_id,variable_url,max_count': '*';
		$sql = 'SELECT '.$fields.' FROM seo_template_variable WHERE `template_id` = '.$tid;
		if($module_id && is_numeric($module_id)){
			$sql .= ' AND module_id = '.$module_id;
		}
		$template_vars = $this->seo_tpl_var_svc->query($sql.' ORDER BY variable_id ASC','All');
		if(empty($template_vars)) {
			$this->_errorResponse(DATA_NOT_FOUND,'模板变量不存在');
			return;
		}
		$this->jsonResponse(array('results' => $template_vars));
	}
	/**
	 * 模板变量新增
	 */
	public function addVarAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_tpl_var_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模板变量新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模板变量更新
	 */
	public function updateVarAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_tpl_var_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模板变量更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	/**
	 * 设置模板变量
	 */
	public function setVarAction() {
		$post = $this->request->getPost();
		$var = $vars = $varids = $varnames = $varcnts = array();
        $tid = intval($post['tid']);
        if(empty($tid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模板不存在');
        	return;
        }
//获取所有模板已经绑定的模块
    $template_mdls = $this->seo_tpl_mdl_svc->getModuleList(array(
      'tm.template_id' => "=".$tid
    ));
    $temp_mode_ids= array();//模块与模板关系id
    $temp_mode_mids= array();//模板绑定的模块id
// 获取模块与模板关系id
    if($template_mdls){
      foreach($template_mdls as $x){
        if(isset($x['id']))
          $temp_mode_ids[] = $x['id'];
        if(isset($x['module_id']))
          $temp_mode_mids[] = $x['module_id'];
      }
    }
//获取模块信息
    $movars = array();//模块参数
    $moinfos =  $this->seo_mdl_var_svc->getModsVars($temp_mode_mids);
    foreach($moinfos as $m){
      if(isset($m['variable_name']) && isset($m['module_id'])){
        array_push($movars, $m['variable_name'] . '_' . $m['module_id']);
      }
    }
    $movars = array_unique($movars);
    $condition['template_id'] = "=".$tid;
		$template_vars = $this->seo_tpl_var_svc->getVarList($condition);
        foreach ($template_vars as $template_var) {
        	$vars[] = $template_var['variable_name'];
        	$varids[$template_var['variable_name']] = $template_var['variable_id'];
        }
        $vardatas = empty($post['vars']) ? array() : json_decode($post['vars'], true);
        foreach ($vardatas as $vardata) {
        	$varnames[] = $vardata['variable_name'];
        	$varcnts[$vardata['variable_name']] = $vardata;
        }
        $result = true;
        $bothvarnames = array_intersect($varnames, $vars);
        foreach ($bothvarnames as $varname) {
        	$varid = $varids[$varname];
        	$var['template_id'] = $tid;
        	$var['variable_name'] = trim($varname);
        	$var['variable_desc'] = trim($varcnts[$varname]['variable_desc']);
        	$var['module_id'] = intval($varcnts[$varname]['module_id']);
        	$var['variable_url'] = trim($varcnts[$varname]['variable_url']);
        	$var['max_count'] = intval($varcnts[$varname]['max_count']);
        	$var['group_id'] = intval($varcnts[$varname]['group_id']);
          $var['group_type'] = $varcnts[$varname]['group_type'];
        	$var['update_time'] = time();
	        $result = $this->seo_tpl_var_svc->update($varid, $var);
        }
        $newvarnames = array_diff($varnames, $vars);
        foreach ($newvarnames as $varname) {
        	$var['template_id'] = $tid;
        	$var['variable_name'] = trim($varname);
        	$var['variable_desc'] = trim($varcnts[$varname]['variable_desc']);
        	$var['module_id'] = intval($varcnts[$varname]['module_id']);
        	$var['variable_url'] = trim($varcnts[$varname]['variable_url']);
        	$var['max_count'] = intval($varcnts[$varname]['max_count']);
        	$var['group_id'] = intval($varcnts[$varname]['group_id']);
          $var['group_type'] = $varcnts[$varname]['group_type'];
        	$var['create_time'] = $var['update_time'] = time();
	        $result = $this->seo_tpl_var_svc->insert($var);
        }
        $oldvarnames = array_diff($vars, $varnames);
// ***********************删除不存在的参数 start **************************
        foreach ($oldvarnames as $varname) {
// 获取重要参数
          preg_match_all("/^mid([0-9]+)_id([0-9]+)_(.*)/", $varname, $newpath, PREG_SET_ORDER);
          if(isset($newpath['0']) && isset($newpath['0']['1']) && isset($newpath['0']['2']) && isset($newpath['0']['3'])){
            //如果绑定的模块或参数不存在则删除
            $newmvar = $newpath['0']['3'] . '_' . $newpath['0']['1'];
            if(!in_array($newpath['0']['2'], $temp_mode_ids) || !in_array($newmvar, $movars)){
               $result = $this->seo_tpl_var_svc->delVarByTid($tid, trim($varname));
               print_r($varname);
              }
          }
        }
// ***********************删除不存在的参数 end **************************
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置模板变量失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	/**
	 * 根据模板模块关系ID获取变量基本信息
	 * @param $variable_id
	 */
	public function getVariableInfoAction(){
		$variable_id = $this->request->get('variable_id');
		if(!$variable_id || !is_numeric($variable_id)) {
			$this->_errorResponse(10001,'请传入正确的模板模块关系ID');
		}
		$info = $this->seo_tpl_var_svc->getOneById($variable_id);
		$this->_successResponse($info);
	}
	/**
	 * 根据模块id模板id和变量类别查询变量名
	 * @param $module_id
	 * @param $template_id
	 * @param $group_type
	 * @return json
	 * @example curl -XGE 'http://ca.lvmama.com/template/getVariableName'
	 */
	public function getVariableNameAction(){
		$module_id = $this->request->get('module_id');
		$template_id = $this->request->get('template_id');
		$group_type = $this->request->get('group_type');
		if(!$module_id || !is_numeric($module_id)) {
			$this->_errorResponse(10001,'请传入正确的模块ID');
		}
		if(!$template_id || !is_numeric($template_id)) {
			$this->_errorResponse(10002,'请传入正确的模板ID');
		}
		$sql = "SELECT variable_id,variable_name FROM seo_template_variable WHERE `module_id` = {$module_id} AND `template_id` = {$template_id}";
		if($group_type){
			$sql .= " AND `group_type` = '{$group_type}'";
		}
		$sql .= ' ORDER BY `variable_id` ASC';
		$info = $this->seo_tpl_var_svc->query($sql,'All');
		$this->_successResponse($info);
	}
	/**
	 * 根据条件获取模板模块关系表中的数据集
	 * @param $module_id
	 * @param $template_id
	 * @param $group_id
	 * @return json
	 * @example curl -XGET 'http://ca.lvmama.com/template/getVariableList'
	 */
	public function getVariableListAction(){
		$module_id = $this->request->get('module_id');
		$template_id = $this->request->get('template_id');
		$group_id = $this->request->get('group_id');
		if(!$module_id && !$template_id && !$group_id){
			$this->_errorResponse(10001,'请传入至少传入一个参数');
		}
		if($module_id && !is_numeric($module_id)){
			$this->_errorResponse(10002,'请传入正确的module_id');
		}
		if($template_id && !is_numeric($template_id)){
			$this->_errorResponse(10003,'请传入正确的template_id');
		}
		if($group_id && !is_numeric($group_id)){
			$this->_errorResponse(10004,'请传入正确的group_id');
		}
		$where = array();
		if($module_id){
			$where['module_id = '] = $module_id;
		}
		if($group_id){
			$where['group_id = '] = $group_id;
		}
		if($template_id){
			$where['template_id = '] = $template_id;
		}
		$list = $this->seo_tpl_var_svc->getVarList($where);
		$this->_successResponse($list);
	}
	/**
	 * 根据条件统计数量
	 * @param $module_id
	 * @param $template_id
	 * @param $group_type
	 * @return json
	 * @example curl -XGET 'http://ca.lvmama.com/template/getNum
	 */
	public function getNumAction(){
		$template_id = $this->request->get('template_id');
		$module_id = $this->request->get('module_id');
		$group_type = $this->request->get('group_type');
		if(!is_numeric($module_id)){
			$this->_errorResponse(10002,'请传入正确的module_id');
		}
		if(!is_numeric($template_id)){
			$this->_errorResponse(10003,'请传入正确的template_id');
		}
		if(!$group_type){
			$this->_errorResponse(10004,'请传入正确的group_type');
		}
		$where = array(
			'template_id' => ' = '.$template_id,
			'module_id' => ' = '.$module_id,
			'group_type' => " = '{$group_type}'"
		);
		$rs = $this->seo_tpl_var_svc->getOneVar($where,' COUNT(*) AS c');
		$this->_successResponse($rs['c']);
	}
	/**
	 * 读取批量生成长尾词页面的过程日志
	 * @param $md5
	 * @param $start_time
	 * @param $start_id
	 */
	public function getBatchLogAction(){
		$md5 = $this->request->get('md5');
		$start_time = $this->request->get('start_time');
		$start_id = $this->request->get('start_id');
		if(empty($md5) || empty($start_time)){
			$this->_errorResponse(10001,'请传入正确的参数');
		}
		$sql = "SELECT * FROM seo_batch_log WHERE `type` = 1 AND `md5` = '{$md5}' AND `createtime` >= '{$start_time}'";
		$sql .= is_numeric($start_id) ? ' AND `id` > '.$start_id : '';
		$sql .= ' LIMIT 10';
		$data = $this->seo_tpl_var_svc->query($sql,'All');
		$this->_successResponse($data);
	}

	/**
	 * 删除2天前的状态
	 */
	public function delBatchLogAction(){
		$del_time = date('Y-m-d h:i:s',strtotime('-2 day'));
		$data = $this->seo_tpl_var_svc->query('DELETE FROM seo_batch_log WHERE createtime < \''.$del_time.'\'');
		$this->_successResponse($data);
	}
	/**
	 * 生成页面
	 */
	public function buildPageTwigAction(){
		$keyword_id = $this->request->get('keyword_id');
		$template_id = $this->request->get('template_id');
		if(empty($keyword_id) || !is_numeric($keyword_id)) $this->_errorResponse(10001,'请传入正确的keyword_id');
		if(empty($template_id) || !is_numeric($template_id)) $this->_errorResponse(10002,'请传入正确的template_id');
		$data_vars = $this->getKeywordVars($keyword_id,$template_id);
		$param = array();
		foreach($data_vars as $x=>$x_val){
			if($x && $x_val != 'null' && $x_val != null){
				$param[] = '{% set ' . $x . ' = ' . $x_val . " %}";
			}
		}
		$param[] = '{{ include(\'template/template-' . $template_id . '.twig\') }}';
		$save_path = $this->di->get('config')->template->path;
		$tname = ($save_path ? $save_path : '/opt/manualTemplate/').'page-' . $keyword_id . '.twig';
		$fs = new LocalAdapter();
		$fs->rm($tname);
		$this->_successResponse($fs->fput($tname,implode("\n",$param)));
	}
	/**
	 * 保存页面时发送消息获取所有出发地产品&生成坑位规则
	 * @param $template_id 模板ID
	 * @param $manualId 页面ID
	 * @param $dest_id 绑定的主目的地ID
	 * @param $keyword_pinyin 页面拼音
	 * @return void
	 * @example curl -i -X 'http://ca.lvmama.com/template/sendMsgGetDistrict'
	 */
	public function sendMsgGetDistrictAction(){
		$template_id = $this->request->get('template_id');
		$manualId = $this->request->get('manualId');
		$dest_id = $this->request->get('dest_id');
		$keyword_pinyin = $this->request->get('keyword_pinyin');
		if(!$template_id || !is_numeric($template_id)){
			$this->_errorResponse(10001,'请传入正确的template_id');
		}
		if(!$manualId || !is_numeric($manualId)){
			$this->_errorResponse(10002,'请传入正确的manualId');
		}
		if(!$dest_id || !is_numeric($dest_id)){
			$this->_errorResponse(10003,'请传入正确的dest_id');
		}
		if(!$keyword_pinyin){
			$this->_errorResponse(10004,'请传入正确的keyword_pinyin');
		}
		$rk = new Producer($this->config->kafka->templateProducer->toArray());
		$data = array(
			'template_id' => $template_id,
			'manualId' => $manualId,
			'dest_id' => $dest_id,
			'keyword_pinyin' => $keyword_pinyin
		);
		$rk->sendMsg(json_encode($data));
	}
	/**
	 * get all keyword static datas
	 * @param  ini $manualId keyword_id
	 * @return array           vars
	 */
	private function getKeywordVars($manualId, $templateId){
		$k_vars = $this->seo_dest_variable_svc->getVarList(array('keyword_id' => '='.$manualId));
		$sql = "SELECT * FROM seo_template_variable WHERE `template_id` = {$templateId} AND `group_type` != 'product' ORDER BY variable_id ASC";
		$template_vars = $this->seo_tpl_var_svc->query($sql,'All');
		$group_type = array();
		foreach($template_vars as $v){
			$group_type[$v['variable_name']] = $v['group_type'];
		}
		$keyword_vars = array();
		foreach ($k_vars as $key => $keyword_var) {
			$var_val = json_decode($keyword_var['variable_content'], true);
			if(is_array($var_val)){
				if(isset($var_val['value'])){
					$var_val = $var_val['value'];
				}
				if(is_array($var_val) && isset($var_val['0']) && $group_type[$keyword_var['variable_name']] != 'tab'){
					if($keyword_var['max_count']){
						$var_val = array_slice($var_val, 0, $keyword_var['max_count']);
					}else{
						$var_val = array_slice($var_val, 0);
					}
				}
			}
			$keyword_vars[$keyword_var['variable_name']] = json_encode($var_val, JSON_UNESCAPED_UNICODE);
		}
		return $keyword_vars;
	}
}
