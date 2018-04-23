<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-3
 * Time: 下午3:26
 */
class QuesTagRelDataService extends DataServiceBase {

    const TABLE_NAME = 'qa_question_tag_rel';
    const PRIMARY_KEY='id';

    /**
     * 获取tag_id对应的question_id
     * @param $tid  tag_id  标签id
     * @param string $type  page|all    all全部 page按条件输出
     * @param int $perpage  每页条数
     * @param int $page 页码
     * @return array
     */
    public function getQidByTid($tid, $type='page', $perpage=10, $page=1){
        $where_condition = array('tag_id'=>"=".$tid);
        if($type == 'page'){
            $limit = array('page_num'=>$page, 'page_size'=>$perpage);
        }else{
            $limit = null;
        }
        $data = $this->getList($where_condition, self::TABLE_NAME, $limit, 'question_id');
        return $data?$data:array();
    }

    /**
     * 获取category_id对应的question_id
     * @param $cid  category_id 分类
     * @param string $type  page|all    all全部 page按条件输出
     * @param int $perpage  每页条数
     * @param int $page 页码
     * @return array
     */
    public function getQidByCid($cid, $type='page', $perpage=10, $page=1){
        // get tag_id
        $where_condition = array('category_id'=>"=".$cid);
        $data = $this->getList($where_condition, 'qa_tag', null, 'id');

        if($data){
            if(count($data)>1){
                $ids = '';
                foreach($data as $val){
                    if($val['id']) $ids .= $val['id'].',';
                }
                if(strlen($ids) > 0){
                    $where = array('tag_id'=>" in (".substr($ids,0,-1).")");
                }else{
                    return array();
                }
            }else{
                $where = array('tag_id'=>"=".$data[0]['id']);
            }
        }else{
            return array();
        }

        if($type == 'page'){
            $limit = array('page_num'=>$page, 'page_size'=>$perpage);
        }else{
            $limit = null;
        }

        // get ques_id
        $res = $this->getList($where,  self::TABLE_NAME, $limit, 'id');

        return $res;

    }

}
