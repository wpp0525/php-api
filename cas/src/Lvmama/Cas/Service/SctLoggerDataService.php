<?php

namespace Lvmama\Cas\Service;

class SctLoggerDataService extends DataServiceBase{
    const TABLE_NAME_PRE = 'lg_';//对应数据库表

    private $expression_map = array(
        'EQ'    => ' = ',
        'NEQ'   => '<>',
        'GT'    => '>',
        'EGT'   => '>=',
        'LT'    => '<',
        'ELT'   => '<=',
        'LIKE'  => 'LIKE',
        'IN'    => 'IN',
    );

    /**
     * 查询数据
     * @param array $params
     * @return array
     */
    public function select($params = array()){
        $init_params = array(
            'table' => '',
            'select' => '*',
            'where' => array(),
            'order' => '',
            'group' => '',
            'limit' => '',
            'page' => array()
        );
        $params = array_merge($init_params, $params);
        $table_name = self::TABLE_NAME_PRE . $params['table'];
        if(!$this->getAdapter()->tableExists($table_name))
            return array('error' => '1','result' => '表未定义');
        $where_arr = $this->parseWhereCondition($params['where']);
        $params['where'] = is_array($where_arr) ? implode(' AND ',$where_arr['where']) : '1';
        $sql = "SELECT {$params['select']} FROM {$table_name} WHERE {$params['where']}";
        if($params['order'])
            $sql .= " ORDER BY {$params['order']}";
        if($params['group'])
            $sql .= " GROUP BY {$params['group']}";
        if($params['page']) {
            $params['page']['pageSize'] = $params['page']['pageSize'] ? $params['page']['pageSize'] : '10';
            $params['page']['page'] = $params['page']['page'] ? $params['page']['page'] : '1';
            $offset = ($params['page']['page'] - 1) * $params['page']['pageSize'];
            $sql .= " LIMIT {$offset},{$params['page']['pageSize']}";
        }elseif ($params['limit']) {
            $limit_arr = explode(',', $params['limit']);
            if (count($limit_arr) == 1)
                $params['limit'] = '0,' . $limit_arr[0];
            else
                $params['limit'] = $limit_arr[0] . ',' . $limit_arr[1];
            $sql .= " LIMIT {$params['limit']}";
        }

        if(isset($where_arr['param']))
            $result = $this->getAdapter()->fetchAll($sql,\PDO::FETCH_ASSOC,$where_arr['param']);
        else
            $result = $this->getAdapter()->fetchAll($sql,\PDO::FETCH_ASSOC);
        $data = array();
        $data['list'] = $result;
        if($params['page']){
            $count_sql = "SELECT count(*) as itemCount FROM {$table_name} WHERE {$params['where']}";
            if(isset($where_arr['param']))
                $count_res = $this->getAdapter()->fetchOne($count_sql,\PDO::FETCH_ASSOC,$where_arr['param']);
            else
                $count_res = $this->getAdapter()->fetchOne($count_sql,\PDO::FETCH_ASSOC);
            $itemCount = $count_res['itemCount'];
            $data['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $params['page']['pageSize']),
                'page' => $params['page']['page'],
                'pageSize' => $params['page']['pageSize']
            );
        }
        return $data;
    }

    /**
     * 新增数据
     * @param array $params
     * @return array
     */
    public function insert($params = array()) {
        $init_params = array(
            'table' => '',
            'data' => array(),
        );
        $params = array_merge($init_params,$params);
        $table_name = self::TABLE_NAME_PRE . $params['table'];
        if(!$this->getAdapter()->tableExists($table_name))
            return array('error' => '1','result' => '表未定义');
        if(empty($params)) return array('error' => '1', 'result' => '未设置插入的值');
        $id = $this->getAdapter()->insert($table_name, array_values($params['data']), array_keys($params['data']));
        if($id)
            return array('error' => '0','result' => $id);
        else
            return array('error' => '1', 'result' => '插入失败');
    }

    /**
     * 更新数据
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $init_params = array(
            'table' => '',
            'where' => '',
            'data' => array(),
        );
        $params = array_merge($init_params,$params);
        $table_name = self::TABLE_NAME_PRE . $params['table'];
        if(!$this->getAdapter()->tableExists($table_name))
            return array('error' => '1','result' => '表未定义');
        if(!trim($params['where']) || empty($params['data']))
            return array('error' => '1','result' => '未设置更新条件或未设置数据');
        $id = $this->getAdapter()->update($table_name, array_keys($params['data']), array_values($params['data']), $params['where']);
        if($id)
            return array('error' => '0','result' => '更新成功');
        else
            return array('error' => '1','result' => '更新失败');
    }

    /**
     * 删除数据
     * @param array $params
     * @return array
     */
    public function delete($params = array()){
        $init_params = array(
            'table' => '',
            'where' => array(),
        );
        $params = array_merge($init_params,$params);
        $table_name = self::TABLE_NAME_PRE . $params['table'];
        if(!$this->getAdapter()->tableExists($table_name))
            return array('error' => '1','result' => $table_name.'表未定义');
        if(empty($params['where']))
            return array('error' => '1','result' => $table_name.'未设置删除条件');
        $data = $this->select($params);
        if(empty($data['list']))
            return array('error' => '1','result' => $table_name.'未找到要删除的记录');
        $where = array();
        foreach($params['where'] as $key => $value){
            $where[] = $key . ' = ' . $value;
        }
        $where_sql = implode(' AND ',$where);
        $this->getAdapter()->delete($table_name, $where_sql);
        return array('error' => '0','result' => $table_name.'删除成功');
    }

    /**
     * 执行原生SQL，请确保数据安全
     * @param $sql
     * @return array
     */
    public function querySql($sql){
        if(empty($sql)) return array('error' => '1','result' => 'SQL 错误');
        $type = trim(substr($sql,0,6));
        if(in_array(strtolower($type), array('insert','update','delete','create'))) {
        	try {
	            $this->getAdapter()->forceMaster();
	            $res = $this->getAdapter()->query($sql);
	            if (empty($res)) {
        			return array('error' => '1','result' => 'SQL('.$sql.')执行错误');
	            } else {
	            	return 'success';
	            }
        	} catch(\Exception $e) {
        		return array('error' => '1','result' => 'SQL('.$sql.')执行错误('.$e->getMessage().')');
        	}
        }
        if (strtolower($type) <> 'select') {
        	return array('error' => '1','result' => 'SQL('.$sql.')错误');
        }
        $resource = $this->getAdapter()->query($sql);
        if (!empty($resource)) {
        	$resource->setFetchMode(\PDO::FETCH_ASSOC);
        	$data = array();
        	$data['list'] = $resource->fetchAll();
        	return $data;
        } else {
        	return array('error' => '1','result' => 'SQL('.$sql.')错误');
        }
    }

    /**
     * 按月建表
     * @param $tbname
     * @return array|bool
     */
    public function createTable($tbname)
    {
        $tbfullname = self::TABLE_NAME_PRE . $tbname;
        if($this->getAdapter()->tableExists($tbfullname))
            return false;
        $sql = "CREATE TABLE IF NOT EXISTS `{$tbfullname}` (
              `logs_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
              `uid` varchar(32) NOT NULL DEFAULT '' COMMENT '用户ID',
              `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名字',
              `controller` varchar(40) NOT NULL DEFAULT '' COMMENT '控制器名',
              `action` varchar(40) NOT NULL DEFAULT '' COMMENT '动作名称',
              `url` text NOT NULL COMMENT '执行操作的URL',
              `post` text NOT NULL COMMENT 'POST的数据',
              `get` text NOT NULL COMMENT 'GET的数据',
              `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
              `ip` char(15) NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP地址',
              `channel` varchar(40) NOT NULL DEFAULT '' COMMENT '所属频道',
              `tbname` varchar(40) NOT NULL DEFAULT '' COMMENT '含义',
              PRIMARY KEY (`logs_id`),
              KEY `channel` (`channel`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        return $this->querySql($sql);

    }

    /**
     * 日志列表
     * @param $params
     * @return array
     */
    public function getLogList($params)
    {
        $init_params = array(
            'select' => '*',
            'order' => 'create_time DESC,logs_id DESC',
            'limit' => '0,15',
        );
        $params = array_merge($init_params, $params);
        if($params['page']){
            $params['limit'] = ($params['page']['page'] - 1) * $params['page']['pageSize'] . ',' . $params['page']['pageSize'];
        }
        if(!$params['where'])
            $params['where'] = '1';
        $tables = $this->parseTable($params['table_prefix'],$params['start_time'],$params['end_time']);

        $select_sql = $count_sql = array();
        foreach ($tables as $table) {
            $select_sql[] = "SELECT {$params['select']} FROM {$table} WHERE {$params['where']}";
            $count_sql[] = "SELECT COUNT(*) AS count FROM {$table} WHERE {$params['where']}";
        }
        $s_sql = implode(' UNION ALL ',$select_sql);
        $c_sql = "SELECT SUM(count) AS total FROM (" . implode(' UNION ALL ',$count_sql) . ') t';

        //获取总数
        $count = $this->querySql($c_sql);
        $pages = array(
            'page' => $params['page']['page'],
            'itemCount' => $count['list']['0']['total'],
            'pageCount' => ceil($count['list']['0']['total'] / $params['page']['pageSize']),
            'pageSize' => $params['page']['pageSize'],
        );

        //获取数据
        if($params['order'])
            $s_sql .= " ORDER BY {$params['order']}";

        if($params['limit'])
            $s_sql .= " LIMIT {$params['limit']}";

        $data_list = $this->querySql($s_sql);

        return array('list' => $data_list['list'],'pages' => $pages);
    }

    /**
     * 解析生成where条件
     * @param array $where
     * @return array|string
     */
    private function parseWhereCondition($where = array()){
        if(empty($where) || !is_array($where)) return '1';
        $res = array();
        foreach($where as $key => $value){
            $tmp = ':' . $key;
            if(is_array($value)){
                $data = $this->parseArray($value);
                foreach($data['symbol'] as $k => $v) {
                    $exp = strtoupper($v);
                    $tmp_key = $key . $k;
                    $tmp_value = ':' . $tmp_key;
                    switch ($exp) {
                        case '=':
                        case '<>':
                        case '!=':
                        case '>':
                        case '>=':
                        case '<':
                        case '<=':
                            $res['where'][] = "`{$key}` {$exp} {$tmp_value}";
                            $res['param'][$tmp_key] = $data['vals'][$k];
                            break;
                        case 'EQ':
                        case 'NEQ':
                        case 'GT':
                        case 'EGT':
                        case 'LT':
                        case 'ELT':
                        case 'LIKE':
                            $res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$tmp_value}";
                            $res['param'][$tmp_key] = $data['vals'][$k];
                            break;
                        case 'IN'://TODO PDO不支持直接绑定IN参数。在使用时确保IN中的数据安全
                            $res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$data['vals'][$k]}";
                            break;
                        default:
                            break;
                    }
                }
            }else {
                $res['where'][] = "`{$key}` = {$tmp}";
                $res['param'][$key] = $value;
            }
        }
        return $res;
    }

    /**
     * 分解一维数组：所有偶数键的值为一个数组，所有奇数键的值为一个数组
     * @param $array
     * @return array
     */
    private function parseArray($array){
        $symbol = $vals = array();
        foreach ($array as $key => $value) {
            if($key % 2 == 0)
                $symbol[] = $value;
            else
                $vals [] = $value;
        }
        return array('symbol' => $symbol,'vals' => $vals);
    }

    /**
     * 返回涉及到的表
     * @param string $table_prefix
     * @param $start_time
     * @param $end_time
     * @return array
     */
    private function parseTable($table_prefix = 'sct_logs',$start_time,$end_time)
    {
        if(!$start_time && !$end_time) {
            $tbname = $table_prefix . '_' . date('Ym', time());
            $this->createTable($tbname);
            return array(self::TABLE_NAME_PRE . $tbname);
        }
        $year_month_list = $this->getYearMonthList($start_time,$end_time);

        $table_list = array();
        foreach ($year_month_list as $value) {
            $table_name = self::TABLE_NAME_PRE . $table_prefix . '_' . $value;
            if(!$this->getAdapter()->tableExists($table_name))
                continue;
            $table_list[] = $table_name;
        }
        return $table_list;
    }

    /**
     * 获取涉及到的年月
     * @param $start_time
     * @param $end_time
     * @return array
     */
    private function getYearMonthList($start_time,$end_time)
    {
        $start_year = intval(date('Y',$start_time));
        $start_month = intval(date('m',$start_time));

        $end_year = intval(date('Y',$end_time));
        $end_month = intval(date('m',$end_time));

        //计算开始时间与结束时间之间所差的年份
        $y_distance = $end_year - $start_year;

        $result = array();
        //同一年
        if($y_distance == 0){
            for($i = $start_month;$i <= $end_month;$i++){
                $result[] = $start_year . sprintf('%02d',$i);
            }
        }

        //相差一年
        if($y_distance == 1){
            for($i = $start_month;$i <= 12;$i++){
                $result[] = $start_year . sprintf('%02d',$i);
            }
            for($j = 1;$j <= $end_month;$j++){
                $result[] = $end_year . sprintf('%02d',$j);
            }
        }

        //相差一年以上
        if($y_distance > 1){
            for($i = $start_month;$i <= 12;$i++){
                $result[] = $start_year . sprintf('%02d',$i);
            }
            for($y = $start_year + 1;$y <= $end_year;$y++){
                for($m = 1;$m <= 12;$m++){
                    $result[] = $y . sprintf('%02d',$m);
                }
            }
            for($j = 1;$j <= $end_month;$j++){
                $result[] = $end_year . sprintf('%02d',$j);
            }
        }
        return $result;
    }

}