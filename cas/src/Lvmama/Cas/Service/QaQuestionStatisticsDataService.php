<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-7-14
 * Time: 下午2:00
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class QaQuestionStatisticsDataService extends DataServiceBase {

//    const TABLE_NAME = 'qa_question_statistics';//对应数据库表
    const TABLE_NAME = 'qa_question_ext';//对应数据库表
    const BEANSTALK_TUBE = '';
    const BEANSTALK_TRIP_MSG = '';
    const PV_REAL = 2;
    const LIKE_INIT = 3;


    /**
     * 获取问题的扩展信息
     * @param $question_id
     * @return bool|mixed
     */
    public function getOneByQId($question_id){

        $where_condition = array('question_id' => "=".$question_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME);

        return $data;
    }



    public function initStatistics($question_id){

        $where_condition = array('question_id' => "=".$question_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME, 'id');

        if(!$data['id']){
            $time = time();
            $params = array(
                'question_id' => $question_id,
                'create_time' => $time,
                'update_time' => $time
            );
            $res = $this->insert($params);
            return $res;
        }else{
            return true;
        }

    }

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

    public function updateStatistics($question_id){

        $where_condition = array('question_id' => "=".$question_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME, 'id');

        $res1 = $this->getOne($where_condition, 'qa_admin_answer', 'count(*) as num');
        $count1 = $res1['num'];

        $where_condition['status'] = "=1";
        $res2 = $this->getOne($where_condition, 'qa_admin_answer', 'count(*) as num');
        $count2 = $res2['num'];

        $time = time();
        if(!$data['id']){
            $params = array(
                'total_answer' => $count1,
                'valid_answer' => $count2,
                'question_id' => $question_id,
                'create_time' => $time,
                'update_time' => $time
            );
            $res = $this->insert($params);
        }else{
            $params = array(
                'total_answer' => $count1,
                'valid_answer' => $count2,
                'update_time' => $time
            );
            $res = $this->update($data['id'], $params);
        }
        return $res;
    }


    /**
     * 社区问答更新 问题基本信息
     * @param $question_id
     * @return array
     */
    public function updateExtInfo($question_id){

        $where_condition = array('question_id' => "=".$question_id);
        $data = $this->getOne($where_condition, self::TABLE_NAME, 'id');

        $res1 = $this->getOne($where_condition, 'qa_answer', 'count(*) as num');
        $count1 = $res1['num'];

        $where_condition['del_status'] = "=0";
        $where_condition['main_status'] = "=5";
        $res2 = $this->getOne($where_condition, 'qa_answer', 'count(*) as num');
        $count2 = $res2['num'];

        $time = time();
        if(!$data['id']){
            $params = array(
                'total_answer' => $count1,
                'valid_answer' => $count2,
                'question_id' => $question_id,
                'create_time' => $time,
                'update_time' => $time
            );
            $res = $this->insert($params);
        }else{
            $params = array(
                'total_answer' => $count1,
                'valid_answer' => $count2,
                'update_time' => $time
            );
            $res = $this->update($data['id'],$params);
        }

        return array('valid_answer' => $count2, 'ext_id' => $res);

    }






}