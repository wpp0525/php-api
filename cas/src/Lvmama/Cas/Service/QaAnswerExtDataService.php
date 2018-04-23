<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-23
 * Time: 上午10:16
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;


class QaAnswerExtDataService extends DataServiceBase {

    const TABLE_NAME = 'qa_answer_ext';//对应数据库表
    const BEANSTALK_TUBE = '';
    const BEANSTALK_TRIP_MSG = '';
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    // 添加
    public function insert($data) {
        $is_ok = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
        if($is_ok){
            $id = $this->getAdapter()->lastInsertId();
            return $id;
        }else{
            return false;
        }
    }

    // 更新
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        $is_ok = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
        if($is_ok){
            return $id;
        }else{
            return false;
        }
    }

    public function initStatistics($answer_id){

        $where_condition = array('answer_id' => "=".$answer_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME, 'id');

        if(!$data['id']){
            $time = time();
            $params = array(
                'answer_id' => $answer_id,
                'create_time' => $time,
                'update_time' => $time
            );
            $res = $this->insert($params);
            return $res;
        }else{
            return true;
        }

    }

    public function updateStatistics($answer_id){

        $where_condition = array('answer_id' => "=".$answer_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME, 'id');

        $where_condition['del_status'] = "= 0";
        $res1 = $this->getOne($where_condition, 'qa_answer_comment', 'count(*) as num');
        $count1 = $res1['num'];

        $where_condition['main_status'] = "=5";
        $res2 = $this->getOne($where_condition, 'qa_answer_comment', 'count(*) as num');
        $count2 = $res2['num'];

        $time = time();
        if(!$data['id']){
            $params = array(
                'total_comment' => $count1,
                'valid_comment' => $count2,
                'answer_id' => $answer_id,
                'create_time' => $time,
                'update_time' => $time
            );
            $res = $this->insert($params);
        }else{
            $params = array(
                'total_comment' => $count1,
                'valid_comment' => $count2,
                'update_time' => $time
            );
            $res = $this->update($data['id'],$params);
        }
        return array('valid_comment' => $count2, 'ext_id' => $res);

    }





}

