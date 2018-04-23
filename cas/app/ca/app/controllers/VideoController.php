<?php


/**
 * 视频游记 控制器
 *
 * @author jianghu
 *
 */
class VideoController extends ControllerBase {

    private $redis;
    private $newtripsvc;

    public function initialize() {
        parent::initialize();
        $this->newtripsvc = $this->di->get('cas')->get('travel_data_service');
        $this->redis = $this->di->get('cas')->getRedis();
    }

    /**
     * 原生SQL
     */
    public function executeSqlAction() {
        $res = array();
        if($this->sql) {
            $res=$this->querySql($this->sql);
        }
        $this->_successResponse($res);
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return mixed
     */
    private function querySql($sql){
        return $this->newtripsvc->querySql($sql);
    }

    /**
     * 查询数据
     */
    public function selectDataAction(){
        $data = array();
        if($this->table) {
            $data['table'] = $this->table;
        }
        if($this->select) {
            $data['select'] = $this->select;
        }
        if($this->where) {
            $data['where'] = unserialize($this->where);
        }
        if($this->order) {
            $data['order'] = $this->order;
        }
        if($this->group) {
            $data['group'] = $this->group;
        }
        if($this->limit) {
            $data['limit'] = $this->limit;
        }
        if($this->page) {
            $data['page'] = unserialize($this->page);
        }
        $res=$this->newtripsvc->select($data);
        $this->_successResponse($res);
    }

    /**
     * 更新数据
     */
    public function updateDataAction(){
        $data = array();
        if($this->table) {
            $data['table'] = $this->table;
        }
        if($this->where) {
            $data['where'] = $this->where;
        }
        if($this->data) {
            $data['data'] = unserialize($this->data);
        }
        $res = $this->newtripsvc->update(array(
            'table' => $data['table'],
            'where' => $data['where'],
            'data' => $data['data'],
        ));
        $this->_successResponse($res);
    }

    /**
     * 新增数据
     * 仅适用于视频游记(多个表关联到一个表的ID)
     * 格式：table和data字段均为数组。
     * 若要关联到主表新插入的ID，则主表在table数组中要处于第一个位置，data数组无顺序要求
     * table => array(table1,table2)
     * data => array(table1 => array(),table2 => array())
     */
    public function createDataAction(){
        $res = array();
        if(!$this->table || !$this->data)
            $this->_errorResponse(100010,'缺少参数');
        $tables = unserialize($this->table);
        $data = unserialize($this->data);
        foreach($tables as $table){
            if(!isset($data[$table])){
                $res[$table] = '0';
                continue;
            }
            if($table !== 'video' && isset($res['video']['result']) && !isset($data[$table]['video_id']))
                $data[$table]['video_id'] = $res['video']['result'];
            $table_res = $this->newtripsvc->insert(array(
                'table' => $table,
                'data' => $data[$table],
            ));
            $res[$table] = $table_res;
        }
        $this->_successResponse($res);
    }
}
