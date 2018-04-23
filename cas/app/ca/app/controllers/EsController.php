<?php
use Lvmama\Common\Utils\UCommon;
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2016/7/20
 * Time: 11:05
 */
class EsController extends ControllerBase
{
    private $es;
    public function initialize(){
        $this->es = $this->di->get('cas')->get('es-data-service');
        $this->api = 'ES';
        return parent::initialize();
    }
    /**
     * 根据指定的点到点名称精确查询相应的目的地ID
     * @param $dest_name
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/es/getDestIdByName
     */
    public function getDestIdByNameAction(){
        $dest_name = isset($this->dest_name) ? $this->dest_name : '';
        if(!$dest_name){
            $this->_errorResponse(10002,'请传入需要搜查询的名称');
        }
        $this->_successResponse(
            $this->es->getIdByName($dest_name)
        );
    }
    /**
     * 根据指定的点到点名称精确查询相应的目的地ID
     * @param $dest_name
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/es/getDestIdsByNames
     */
    public function getDestIdsByNamesAction(){
        $dest_names = isset($this->dest_names) ? addslashes(urldecode($this->dest_names)) : '';
        $fields = isset($this->fields) && $this->fields ? $this->fields : '';
        if(!$dest_names){
            $this->_errorResponse(10002,'请传入需要搜查询的名称');
        }
        $names = explode(',',$dest_names);
        if(count($names) > 1000){
            $this->_errorResponse(10003,'传入的目的地名称一次不能超过1000个');
        }
        $this->_successResponse(
            $this->es->getIdsByNames($names,explode(',',$fields))
        );
    }
    /**
     * 根据关键字及查询条件从ES中获取内容
     * @param $keyword 关键字
     * @param $fields 需要显示的字段名,不传为全部
     * @param $like_fields 需要模糊查询的字段
     * @param $not_in 需要排除的指定字段的值(类似！=)
     * @param $where 需要查询的指定的字段(类似=)
     * @param $order 根据字段排序(order=dest_id:asc)
     * @param $group 根据字段值分组
     * @param $page 显示的页码
     * @param $pageSize 每页显示的条数
     * @return string | json
     * @example curl -i -X POST http://php-api.lvmama.com/es/ajaxWaySearch
     */
    public function ajaxWaySearchAction(){
        $keyword = isset($this->keyword) ? urldecode($this->keyword) : '';
        $fields = isset($this->fields) && $this->fields ? $this->fields : '';
        $like_fields = isset($this->like_fields) && $this->like_fields ? $this->like_fields : 'dest_name,pinyin,short_pinyin';
        $not_in = isset($this->not_in) && $this->not_in ? urldecode($this->not_in) : '';
        $where = isset($this->where) && $this->where ? urldecode($this->where) : '';
        $order = isset($this->order) && $this->order ? $this->order : '';
        $group = isset($this->group) && $this->group ? $this->group : '';
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 5;
        if(!$keyword){
            $this->_errorResponse(10002,'请传入需要搜索的关键字');
        }
        if($page < 1) $page = 1;
        if($pageSize > 50) $pageSize = 50;
        $return = array();
        $must_not = array();
        $order_by = array();
        $group_by = array();
        if(!$keyword){
            return $return;
        }
        if($not_in){
            foreach(explode(',',$not_in) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $must_not[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($order){
            foreach(explode(',',$order) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $order_by[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($group){
            $group_by = explode(',',$group);
        }
        $_where = array();
        foreach(explode(',',$where) as $w){
            $tmp = explode(':',$w);
            if(isset($tmp[0]) && $tmp[0] && isset($tmp[1]) && $tmp[1]){
                $_where[$tmp[0]] = $tmp[1];
            }
        }
        $this->_successResponse(
            $this->es->getDest(
                $keyword,
                $must_not,
                $fields,
                $like_fields,
                $_where,
                $order_by,
                $group_by,
                array('page' => $page,'pageSize' => $pageSize)
            )
        );
    }

    /**
     * 根据文章内容分析出目的地词库中的内容
     * @param content 文章内容
     * @return array
     */
    public function articleAction(){
        $content = isset($this->content) ? urldecode($this->content) : '';
        $return = $this->es->getArticleWord($content);
        $this->_successResponse($return);
    }

    /**
     * 测试使用
     */
    public function testAction(){
        $return = $this->es->getLike();
        $this->_successResponse($return);
    }
    /**
     * 根据关键字及查询条件从ES中获取内容
     * @param $keyword 关键字
     * @param $fields 需要显示的字段名,不传为全部
     * @param $like_fields 需要模糊查询的字段
     * @param $not_in 需要排除的指定字段的值(类似！=)
     * @param $where 需要查询的指定的字段(类似=)
     * @param $order 根据字段排序(order=dest_id:asc)
     * @param $group 根据字段值分组
     * @param $page 显示的页码
     * @param $pageSize 每页显示的条数
     * @param $index 索引名
     * @param $type 类型名
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/es/waySearch
     */
    public function waySearchAction(){
        $keyword = isset($this->keyword) ? urldecode($this->keyword) : '';
        $fields = isset($this->fields) && $this->fields ? $this->fields : '';
        $like_fields = isset($this->like_fields) && $this->like_fields ? $this->like_fields : 'dest_name,pinyin,short_pinyin';
        $not_in = isset($this->not_in) && $this->not_in ? urldecode($this->not_in) : '';
        $where = isset($this->where) && $this->where ? urldecode($this->where) : '';
        $order = isset($this->order) && $this->order ? $this->order : '';
        $group = isset($this->group) && $this->group ? $this->group : '';
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 5;
        $index = isset($this->index) && $this->index ? $this->index : 'lmm_lvyou';
        $type = isset($this->type) && $this->type ? $this->type : 'ly_destination,ly_district_sign';
        if(!$keyword){
            $this->_errorResponse(10002,'请传入需要搜索的关键字');
        }
        if($page < 1) $page = 1;
        if($pageSize > 35) $pageSize = 35;
        $return = array();
        $must_not = array();
        $order_by = array();
        $group_by = array();
        if(!$keyword){
            return $return;
        }
        if($not_in){
            foreach(explode(',',$not_in) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $must_not[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($order){
            foreach(explode(',',$order) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $order_by[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($group){
            $group_by = explode(',',$group);
        }
        $_where = array();
        foreach(explode(',',$where) as $w){
            $tmp = explode(':',$w);
            if(isset($tmp[0]) && $tmp[0] && isset($tmp[1]) && $tmp[1]){
                $_where[$tmp[0]] = $tmp[1];
            }
        }
        $this->_successResponse(
            $this->es->getWaySearch(
                $keyword,
                $must_not,
                $fields,
                $like_fields,
                $_where,
                $order_by,
                $group_by,
                array('page' => $page,'pageSize' => $pageSize),
                $type,
                $index
            )
        );
    }

    /**
     * 查询游记索引中的数据
     * @param keyword 关键字
     * @param fields 需要显示的字段名
     * @param like_fields 需要搜索的字段名
     * @param not_in 必须不存在的值(示例username:客服)
     * @param where 必须存在的值(示例username:小小)
     * @param order 需要排序的字段及排序规则(示例:id:asc)
     * @param group 需要做排重或者分词统计
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return string json
     * @example curl -i -X POST http://ca.lvmama.com/es/getTravelData
     */
    public function getTravelDataAction(){
        $fields = isset($this->fields) && $this->fields ? $this->fields : '';
        $not_in = isset($this->not_in) && $this->not_in ? urldecode($this->not_in) : '';
        $where = isset($this->where) && $this->where ? urldecode($this->where) : '';
        $order = isset($this->order) && $this->order ? $this->order : '';
        $group = isset($this->group) && $this->group ? $this->group : '';
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 5;
        $start_time = isset($this->start_time) ? strtotime($this->start_time) : 0;
        $end_time = isset($this->end_time) ? strtotime($this->end_time) : 0;
        if($page < 1) $page = 1;
        $must_not = array();
        $order_by = array();
        $group_by = array();
        if($not_in){
            foreach(explode(',',$not_in) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $must_not[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($order){
            foreach(explode(',',$order) as $val){
                $tmp = explode(':',$val);
                if(isset($tmp[0]) && isset($tmp[1])){
                    $order_by[$tmp[0]] = $tmp[1];
                }
            }
        }
        if($group){
            $group_by = explode(',',$group);
        }
        $_where = array();
        foreach(explode(',',$where) as $w){
            $tmp = explode(':',$w);
            if(isset($tmp[0]) && $tmp[0] && isset($tmp[1]) && $tmp[1]){
                $_where[$tmp[0]] = $tmp[1];
            }
        }
        $range = array();
        if($start_time && $end_time){
            $range['start_time'] = $start_time;
            $range['end_time'] = $end_time;
        }
        $this->_successResponse(
            $this->es->getTravelData(
                $must_not,
                $fields,
                $_where,
                $order_by,
                $group_by,
                array('page' => $page,'pageSize' => $pageSize),
                $range
            )
        );
    }
    /**
     * 查询产品问答和社区问答的内容
     * @param keyword 关键字
     * @param type 1社区问答,0产品问答
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return string json
     * @example curl -i -X POST http://ca.lvmama.com/es/getQaQuestion
     */
    public function getQaQuestionAction(){
        $keyword    = isset($this->keyword) ? urldecode($this->keyword) : '';
        $type       = isset($this->type) ? intval($this->type) : 1;
        $page       = isset($this->page) ? intval($this->page) : 1;
        $pageSize   = isset($this->pageSize) ? intval($this->pageSize) : 15;
        $fields     = isset($this->fields) ? $this->fields : '';
        if(!$keyword){
            $this->_errorResponse(10002,'请传入需要搜索的关键字');
        }
        if($pageSize < 1 || $pageSize > 50){
            $this->_errorResponse(10003,'每页显示条数需在50条内');
        }
        $fields = $fields ? '"'.str_replace(',','","',$fields).'"' : '"id","uid","username","title","content","auditor_id","audit_time","main_status","recommend_status","del_status","create_time","update_time"';
        $this->_successResponse($this->es->getQaQuestion($keyword, $type, $page, $pageSize,$fields));
    }
    /**
     * 查询vst基础数据
     * @param keyword 关键字
     * @param like_fields 需要查询的字段名
     * @param fields 需要显示的字段名称
     * @param type 查询的类型名
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return string json
     * @example curl -i -X POST http://ca.lvmama.com/es/getVst
     */
    public function getVstAction(){
        $keyword    = isset($this->keyword) ? urldecode($this->keyword) : '';
        $like_fields= isset($this->like_fields) && $this->like_fields ? $this->like_fields : '';
        $filter     = isset($this->filter) && $this->filter ? $this->filter : '';
        $where      = isset($this->where) && $this->where ? $this->where : '';
        $type       = isset($this->type) && $this->type ? $this->type : '';
        $page       = isset($this->page) ? intval($this->page) : 1;
        $pageSize   = isset($this->pageSize) ? intval($this->pageSize) : 15;
        $fields     = isset($this->fields) ? $this->fields : '';
        $order      = isset($this->order) ? $this->order : '';
//        if(!$keyword){
//            $this->_errorResponse(10002,'请传入需要搜索的关键字');
//        }
        if($pageSize < 1 || $pageSize > 50){
            $this->_errorResponse(10003,'每页显示条数需在50条内');
        }
        $this->_successResponse($this->es->getVst($keyword, $like_fields,$fields,$where,$filter,$page, $pageSize,$type,$order));
    }

    /**
     * 备份快照
     */
    public function snapshotAction(){
        $this->_successResponse($this->es->saveSanpShot());
    }
    /**
     * 更新目的地热门景点数据及排序
     */
    public function hotResultAction(){

    }
}