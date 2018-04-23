<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class ModuleDataServiceBase extends DataServiceBase{
    const TABLE_NAME_PRE = 'mo_';//对应数据库表

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
            return array('error' => '0','result' => $this->getAdapter()->lastInsertId());
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
            return array('error' => '1','result' => '表未定义');
        if(empty($params['where']))
            return array('error' => '1','result' => '未设置删除条件');

        $data = $this->select($params);
        if(empty($data['list']))
            return array('error' => '1','result' => '未找到要删除的记录');

        $where = array();
        foreach($params['where'] as $key => $value){
            $where[] = $key . ' = ' . $value;
        }
        $where_sql = implode(' AND ',$where);
        $this->getAdapter()->delete($table_name, $where_sql);
        return array('error' => '0','result' => '删除成功');
    }

    /**
     * 执行原生SQL，请确保数据安全
     * @param $sql
     * @return array
     */
    public function querySql($sql){
        if(!$sql) return array('error' => '1','result' => 'SQL 错误');
        $resource = $this->getAdapter()->query($sql);
        $type = substr($sql,0,6);
        if(in_array(strtolower($type),array('insert','update','delete')))
            return 'success';
        $resource->setFetchMode(\PDO::FETCH_ASSOC);
        $data = array();
        $data['list'] = $resource->fetchAll();
        return $data;
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
                $exp = strtoupper($value['0']);
                switch($exp){
                    case '=':
                    case '<>':
                    case '!=':
                    case '>':
                    case '>=':
                    case '<':
                    case '<=':
                        $res['where'][] = "`{$key}` {$exp} {$tmp}";
                        $res['param'][$key] = $value['1'];
                        break;
                    case 'EQ':
                    case 'NEQ':
                    case 'GT':
                    case 'EGT':
                    case 'LT':
                    case 'ELT':
                    case 'LIKE':
                        $res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$tmp}";
                        $res['param'][$key] = $value['1'];
                        break;
                    case 'IN'://TODO PDO不支持直接绑定IN参数。在使用时确保IN中的数据安全
                        $res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$value['1']}";
                        break;
                    default:
                        break;
                }
            }else {
                $res['where'][] = "`{$key}` = {$tmp}";
                $res['param'][$key] = $value;
            }
        }
        return $res;
    }
}