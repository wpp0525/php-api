<?php
use Lvmama\Common\Utils\UCommon;
/**
 * 大目的地关键词控制器
 *
 * @author flash.guo
 *
 */
class KeywordController extends ControllerBase {
    private $seo_dest_category_svc;
    private $seo_dest_keyword_svc;
    private $seo_dest_variable_svc;
    public function initialize() {
        parent::initialize();
        $this->seo_dest_category_svc	= $this->di->get('cas')->get('seo_dest_category_service');
        $this->seo_dest_keyword_svc		= $this->di->get('cas')->get('seo_dest_keyword_service');
        $this->seo_dest_variable_svc	= $this->di->get('cas')->get('seo_dest_variable_service');
        $this->dest = $this->di->get('cas')->get('destination-data-service');
        $this->seo_tpl_var_svc = $this->di->get('cas')->get('seo_template_variable_service');
    }

	/**
	 * 大目的地关键词详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $keyname = trim($this->request->get('keyname'));
        $cateid = intval($this->request->get('cateid'));
        $status = intval($this->request->get('status'));
        $conditions = array();
        !empty($id) && $conditions['keyword_id'] = "=" . $id;
        !empty($keyname) && $conditions['keyword_name'] = "='" . $keyname . "'";
        !empty($cateid) && $conditions['category_id'] = "=" . $cateid;
        !empty($status) && $conditions['status'] = "=" . $status;
        !empty($conditions) && $keyword_info = $this->seo_dest_keyword_svc->getOneKeyword($conditions);
        if(empty($keyword_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词信息不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $keyword_info));
	}

	/**
	 * 大目的地关键词列表
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
        $keyword_info = $this->seo_dest_keyword_svc->getKeywordList($condition, $limit, $columns, $order);
        if(empty($keyword_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词信息不存在');
        	return;
        }
		foreach($keyword_info as $k=>$v){
			$dest = $this->dest->getDestById($v['dest_id']);
			$keyword_info[$k]['dest_name'] = $dest['dest_name'];
		}
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $keyword_info));
        	return;
        }
        $total_records = $this->seo_dest_keyword_svc->getKeywordTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $keyword_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 大目的地关键词新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_dest_keyword_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 大目的地关键词更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        $info = $this->seo_dest_keyword_svc->getOneById($id);
        if(!$info){
          $this->_errorResponse(OPERATION_FAILED,'关键字不存在');
          return;
        }
        $template_id = isset($info['template_id'])?$info['template_id']:'o';
        $new_tid = isset($post['template_id'])?$post['template_id']:'n';
        if($template_id != $new_tid)
          $this->seo_dest_variable_svc->delAllVarByKid($id);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_dest_keyword_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 大目的地关键词分类详情
	 */
	public function infoCateAction() {
        $id = intval($this->request->get('id'));
        $catename = trim($this->request->get('catename'));
        $cateurl = trim($this->request->get('cateurl'));
        $conditions = array();
        !empty($id) && $conditions['category_id'] = "=" . $id;
        !empty($catename) && $conditions['category_name'] = "='" . $catename . "'";
        !empty($cateurl) && $conditions['category_url'] = "='" . $cateurl . "'";
        !empty($conditions) && $cate_info = $this->seo_dest_category_svc->getOneCate($conditions);
        if(empty($cate_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词分类不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $cate_info));
	}

	/**
	 * 大目的地关键词分类列表
	 */
	public function listCateAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $condition = json_decode($condition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $cate_info = $this->seo_dest_category_svc->getCateList($condition, $limit);
        if(empty($cate_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词分类不存在');
        	return;
        }
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $cate_info));
        	return;
        }
        $total_records = $this->seo_dest_category_svc->getCateTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $cate_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	/**
	 * 大目的地关键词分类新增
	 */
	public function addCateAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_dest_category_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词分类新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 大目的地关键词分类更新
	 */
	public function updateCateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_dest_category_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词分类更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 大目的地关键词分类删除
	 */
	public function deleteCateAction() {
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
        	$result = $this->seo_dest_category_svc->delete($id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词分类删除失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 获取大目的地关键词所有变量
	 */
	public function getVarAction() {
        $kid = intval($this->request->get('kid'));
        if (empty($kid)) {
	        $this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词变量不存在');
	        return;
		}
        $module_id = $this->request->get('module_id');
        $condition = array('keyword_id' => '='.$kid);
		if($module_id && is_numeric($module_id)) $condition['module_id'] = '='.$module_id;
		$keyword_vars = $this->seo_dest_variable_svc->getVarList($condition);
		if(empty($keyword_vars)) {
			$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词变量不存在');
			return;
		}
		$this->jsonResponse(array('results' => $keyword_vars));
	}
    /**
     * 根据变量id获取变量内容
     */
    public function getVarByIdAction(){
        $variable_id = $this->request->get('variable_id');
        if(!$variable_id || !is_numeric($variable_id)) $this->_errorResponse(10001,'variable_id异常');
        $row = $this->seo_dest_variable_svc->getOneById($variable_id);
        $this->_successResponse($row);
    }

	/**
	 * 大目的地关键词变量新增
	 */
	public function addVarAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_dest_variable_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词变量新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result,'error' => 0));
	}

    /**
    * 更新变量入库
    * @return [array] [kerword var info]
    */
    public function holdVarsAction(){
        $keyword_id = $this->request->getPost('keyword_id');
        $variable_name = $this->request->getPost('variable_name');
        $variable_url = $this->request->getPost('variable_url');
        $group_type = $this->request->getPost('group_type');
        $module_id = $this->request->getPost('module_id');
        $max_count = intval($this->request->getPost('max_count'));
        if (!$keyword_id || !$variable_name) $this->_errorResponse(10003, '参数输入错误');
        $variable_content = $variable_url;
        $keyword_info = $this->seo_dest_keyword_svc->getOneKeyword(array('keyword_id = ' => $keyword_id));
        $dest_ids = isset($keyword_info['dest_id']) ? $keyword_info['dest_id'] : '';
        if (!$dest_ids) $this->_errorResponse(10004, '页面没绑定目的地ID');
        $tmp = explode(',', $dest_ids);
        $dest_id = $tmp[0];
        if (preg_match('/^http[s]{0,1}:\/\/.*/', trim($variable_url)) && $group_type != 'product') {
            $url = explode('?', $variable_url);
            $url = $url[0] . '?dest_id=' . $dest_id . '&keyword_id=' . $keyword_id . '&' . (isset($url[1]) ? $url[1] : '');
            $api_content = json_decode(UCommon::curl($url, 'GET'), true);
            if ($api_content && isset($api_content['error']) && !$api_content['error']) {
                $variable_content = $api_content['result'];
            }
        }
        $variable_filter = array();
        //添加默认的筛选项内容
        if ($group_type == 'tab') {
            $rs = json_decode(UCommon::curl('http://ca.lvmama.com/bigdest/getTypeByUrl', 'GET', array('url' => urlencode($variable_url))), true);
            if (!$rs['error']) {
                $routeType = $rs['result']['routeType'];
                $type = $rs['result']['type'];
                foreach ($variable_content as $k => $v) {
                    $filter = array();
                    if ($routeType) $filter['routeType'] = $routeType;
                    if ($type && $v['id']) $filter[$type] = $v['id'];
                    if ($filter) $variable_filter[$k + 1] = $filter;
                }
            }
        }
        if (!is_array($variable_content) && !is_array(json_decode($variable_content, true))) {
            $variable_content = json_encode(array('value' => $variable_content), JSON_UNESCAPED_UNICODE);
        } else {
            if (is_array($variable_content)) {
                $variable_content = json_encode($variable_content, JSON_UNESCAPED_UNICODE);
            }
            $variable_filter = $variable_filter ? json_encode($variable_filter, JSON_UNESCAPED_UNICODE) : '';
        }
        if (!$variable_content) $this->_errorResponse(10005, '更新错误');
        //如果是导航数据，存储到对应数据库
        if (in_array($group_type, array('onenavigation', 'navigation'))) {
            UCommon::curl('http://ca.lvmama.com/tvars/navgationimport', 'POST', array(
                'var_content' => $variable_content,
                'var_name' => $variable_name,
                'keyword_id' => $keyword_id
            ));
        }
        $info = $this->seo_dest_variable_svc->getOneVar(array(
            'variable_name' => "='".$variable_name."'",
            'keyword_id' => "='".$keyword_id."'",
        ));
        if(empty($info['variable_id'])){//不存在,新增
            $this->seo_dest_variable_svc->insert(array(
                'variable_content' => $variable_content,
                'variable_filter' => $variable_filter ? $variable_filter : '',
                'create_time' => time(),
                'variable_name' => $variable_name,
                'keyword_id' => $keyword_id,
                'module_id' => $module_id,
                'group_type' => $group_type,
                'max_count' => $max_count,
            ));
            $this->_successResponse('holdVars success');
        }
        if( $info['group_type'] == 'tab'){
            $v_content_old = json_decode($info['variable_content'], true);
            $post_content = json_decode($variable_content, true);
            $v_content_old = is_array($v_content_old) ? $v_content_old : array();
            $post_content = is_array($post_content) ? $post_content : array();
            $variable_content = json_encode($v_content_old+$post_content, JSON_UNESCAPED_UNICODE);
            $variable_filter_old = json_decode($info['variable_filter'],true);
            $variable_filter_new = json_decode($variable_filter,true);
            $variable_filter = json_encode($variable_filter_old+$variable_filter_new,JSON_UNESCAPED_UNICODE);
        }
        $save_data = array(
            'variable_content' => $variable_content ? $variable_content : '',
            'update_time' => time(),
            'group_type' => $group_type,
            'max_count' => $max_count,
            'variable_filter' => $variable_filter ? $variable_filter : ''
        );
        $this->seo_dest_variable_svc->update($info['variable_id'], $save_data);
        $this->_successResponse('update Vars success');
    }

	/**
	 * 大目的地关键词变量更新
	 */
	public function updateVarAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_dest_variable_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'大目的地关键词变量更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
    /**
     * 删除指定页面的关键词变量
     * @param keyword_id
     * @return json
     * @example curl -XGET 'http://ca.lvmama.com/keyword/deleteByKid'
     */
    public function deleteByKidAction(){
        $kid = $this->request->get('keyword_id');
        if(!$kid || !is_numeric($kid)) $this->_errorResponse(10001,'请传入正确的目的地id');
        $rs = $this->seo_dest_variable_svc->delVarByKid($kid);
        $this->_successResponse($rs);
    }

	/**
	 * 设置大目的地关键词变量
	 */
	public function setVarAction() {
		$post = $this->request->getPost();
		$var = $vars = $varids = $varnames = $varcnts = array();
        $kid = intval($post['kid']);
        if(empty($kid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'大目的地关键词信息不存在');
        	return;
        }
		$condition['keyword_id'] = "=".$kid;
		$keyword_vars = $this->seo_dest_variable_svc->getVarList($condition);
        foreach ($keyword_vars as $keyword_var) {
        	$vars[] = $keyword_var['variable_name'];
        	$varids[$keyword_var['variable_name']] = $keyword_var['variable_id'];
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
        	$var['keyword_id'] = $kid;
        	$var['variable_name'] = trim($varname);
        	$var['module_id'] = intval($varcnts[$varname]['module_id']);
        	$var['variable_content'] = trim($varcnts[$varname]['variable_content']);
        	$var['variable_filter'] = trim($varcnts[$varname]['variable_filter']);
        	$var['update_time'] = time();
	        $result = $this->seo_dest_variable_svc->update($varid, $var);
        }
        $newvarnames = array_diff($varnames, $vars);
        foreach ($newvarnames as $varname) {
        	$var['keyword_id'] = $kid;
        	$var['variable_name'] = trim($varname);
        	$var['module_id'] = intval($varcnts[$varname]['module_id']);
        	$var['variable_content'] = trim($varcnts[$varname]['variable_content']);
        	$var['variable_filter'] = trim($varcnts[$varname]['variable_filter']);
        	$var['create_time'] = $var['update_time'] = time();
	        $result = $this->seo_dest_variable_svc->insert($var);
        }
        $oldvarnames = array_diff($vars, $varnames);
        foreach ($oldvarnames as $varname) {
	        $result = $this->seo_dest_variable_svc->delVarByKid($kid, trim($varname));
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置大目的地关键词变量失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

    /**
     * 检查目的地ID是否在是否在大目的地页面使用过
     * 检查长尾词名称是否已经存在
     * @param dest_id
     * @param keyword_id页面ID
     * @param keyword_name 关键词名称
     * @param keyword_pinyin 关键词拼音
     * @param long_tail 0=>大目的地页面,1=>长尾词页面
     * @author shenxiang
     */
    public function existsTourAction(){
        $dest_id = $this->request->get('dest_id');
        $keyword_id = $this->request->get('keyword_id');
        $keyword_name = $this->request->get('keyword_name');
        $keyword_pinyin = $this->request->get('keyword_pinyin');
        $long_tail = $this->request->get('long_tail');
        $long_tail = is_numeric($long_tail) ? $long_tail : 0;
        if(!$keyword_id || !is_numeric($keyword_id)) $keyword_id = 0;
        if($long_tail == 0 && (!$dest_id || !is_numeric($dest_id))){
            $this->_errorResponse(10001,'大目的地关键词信息不存在');
        }
        if($long_tail == 1 && (!$keyword_name || !$keyword_pinyin)) $this->_errorResponse(10002,'长尾词名称和拼音不能为空');

        $where = array('long_tail = ' => $long_tail,'keyword_id !=' => $keyword_id);
        if($dest_id) $where['dest_id'] = ' = '.$dest_id;
        if($keyword_pinyin) $where['keyword_pinyin'] = " = '{$keyword_pinyin}'";
        $row = $this->seo_dest_keyword_svc->getOneKeyword($where);
        $this->_successResponse($row);
    }
    /**
     * 根据变量名称和关键字ID获取变量ID
     */
    public function getVarIdByVarKeyAction(){
        $variable_name = $this->request->get('variable_name');
        $keyword_id = $this->request->get('keyword_id');
        if(!$variable_name){
            $this->_errorResponse(10001,'参数variable_name不能为空');
        }
        if(!$keyword_id || !is_numeric($keyword_id)){
            $this->_errorResponse(10002,'参数keyword_id不符合要求');
        }
        $where = array('variable_name' => " = '{$variable_name}'",'keyword_id' => '='.$keyword_id);
        $row = $this->seo_dest_variable_svc->getOneVar($where);
        $this->_successResponse($row);
    }
	/**
     * 根据变量模块ID、关键字ID和变量类型获取指定内容
	 * @param $module_id 模块ID
	 * @param $keyword_id 关键字ID
	 * @param @group_type 参数类型
	 * @return json
	 * @example curl -XGET 'http://ca.lvmama.com/keyword/getVarByModuleKeywordGroup'
     */
    public function getVarByModuleKeywordGroupAction(){
        $module_id = $this->request->get('module_id');
        $keyword_id = $this->request->get('keyword_id');
		$group_type = $this->request->get('group_type');
        if(!is_numeric($module_id)){
            $this->_errorResponse(10001,'参数module_id不在符合要求');
        }
        if(!is_numeric($keyword_id)){
            $this->_errorResponse(10002,'参数keyword_id不符合要求');
        }
		if(!$group_type){
            $this->_errorResponse(10003,'参数group_type不符合要求');
        }
        $where = array('keyword_id' => '='.$keyword_id,'module_id' => " = '{$module_id}'",'group_type' => " = '{$group_type}'");
        $row = $this->seo_dest_variable_svc->getOneVar($where);
        $this->_successResponse($row);
    }



  public function batchupdateLongtailAction(){
      if(!$Longtail = $this->request->getPost('Longtail')){
          return $this->_errorResponse(4004,'无法找到传入的参数');
      }
      $md5 = md5($Longtail);
      $Longtail = json_decode($Longtail, true);
      if(!is_array($Longtail)){
          return $this->_errorResponse(4004,'传入参数有误');
      }
      $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['newLongTailProducer']);
      foreach($Longtail as $indata){
              $indata['long_tail'] = 1;
              $indata['status'] = 0;
              $indata['create_time'] = time();
              $indata['update_time'] = time();
          $filters = empty($indata['filters']) ? '' : $indata['filters'];
          unset($indata['filters']);
              $this->seo_dest_keyword_svc->insert($indata);
              $new_keyword_id = $this->seo_dest_keyword_svc->lastInsertId();
              $kafka->sendMsg(json_encode(array(
                'keyword_id' => $new_keyword_id,
                'template_id' => $indata['template_id'],
                'md5' => $md5,
                  'filters' => $filters
              )));
      }
      $this->_successResponse($md5);
  }
}
