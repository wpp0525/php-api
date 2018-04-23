<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-7-15
 * Time: 下午3:00
 */
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\QaQuestionDataService;
use Lvmama\Cas\Service\QaQuestionStatisticsDataService;

class QashowController extends ControllerBase {

    private $qa_svc;

    private $product_bu_array = array(1 => '国内BU', 2 => '出境BU', 3 => '目的地BU', 4 => '门票BU', 5 => '商旅BU');
    private $product_cate_array = array(13 => '门票', 18 => '自由行', 24 => '跟团游（境内）', 28 => '当地游', 33 => '邮轮', 39 => '签证', 45 => 'wifi/电话卡');
    private $cate_tag_array = array(
        'cate' => array(3 => '门票', 4 => '自由行', 5 => '跟团游（境内）', 6 => '当地游', 7 => '邮轮', 8 => '签证', 9 => 'wifi/电话卡'),
        'tag' => array(
            '3' => array(13 => '常见问题', 14 => '付款支付', 15 => '活动促销', 16 => '取票入园', 17 => '景点相关'),
            '4' => array(18 => '常见问题', 19 => '付款支付', 20 => '活动促销', 21 => '酒店住宿', 22 => '往返交通', 23 => '景点相关'),
            '5' => array(24 => '常见问题', 25 => '付款支付', 26 => '活动促销', 27 => '酒店住宿'),
            '6' => array(28 => '常见问题', 29 => '付款支付', 30 => '活动促销', 31 => '交通接送', 32 => '导游'),
            '7' => array(33 => '常见问题', 34 => '付款支付', 35 => '活动促销', 36 => '签证办理', 37 => '线路相关', 38 => '邮轮信息'),
            '8' => array(39 => '常见问题', 40 => '付款支付', 41 => '活动促销', 42 => '材料提交', 43 => '办理时间', 44 => '取签送签'),
            '9' => array(45 => '常见问题', 46 => '付款支付', 47 => '活动促销', 48 => '设备拿取')
        )
    );

    public function initialize() {
        parent::initialize();
        $this->qa_svc = $this->di->get('cas')->get('qa_common_data_service');
        $this->redis_svc=$this->di->get('cas')->get('redis_data_service');
    }

    public function testAction(){
        $this->redis_svc->setQuestionTagRel();
    }


    public function getProductQaListAction(){

        $product_id = $this->productId;
//        $bu_id = $this->buId;
//        $cate_id = $this->cateId;
        $tag_id = $this->tagId;
        $page = $this->page;
        $pageSize = $this->pageSize;

//        $product_id = 2;
//        $bu_id = 4;
//        $cate_id = 4;
//        $tag_id = '';
//        $page = 1;
//        $pageSize = 15;
//        echo $product_id."====".$bu_id."====".$cate_id."====".$tag_id."====".$page."====".$pageSize; die;

        if(!$product_id){
            $this->qa_svc->messageOutput('400');
        }

        $cate_id = $this->getCateIdByPid($product_id);

        if($cate_id){

            $wherein = array();
            $where = array(
                'qpr.product_id' => $product_id,
                'q.main_status' => 5,
                'aa.status' => 1
            );

            if(!$tag_id){
                $wherein['qtr.tag_id'] = "('".implode("', '", array_keys($this->cate_tag_array['tag'][$cate_id]))."')";
            }else{
                $where['qtr.tag_id'] = $tag_id;
            }

//            var_dump(array('pageSize' => $pageSize, 'page' => $page));die;

            // 组成查询全部条件
            $params_condition = array(
                'table' =>'qa_question q',
                'select' => 'q.id, qpr.product_id, q.content, aa.content as answer',
                'join' => array(
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_question_product_rel qpr',
                        'on' => 'q.id = qpr.question_id',
                    ),
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_question_tag_rel qtr',
                        'on' => 'q.id = qtr.question_id',
                    ),
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_admin_answer aa',
                        'on' => 'q.id = aa.question_id',
                    ),
                ),
                'in' =>$wherein,
                'where' => $where,
                'order' => 'q.update_time desc',
                'group' => 'q.id',
                'page' => array('pageSize' => $pageSize, 'page' => $page)
            );

            $res = $this->qa_svc->getByParams($params_condition);
            $res['tags'] = $this->cate_tag_array['tag'][$cate_id];

            $this->qa_svc->messageOutput('200', $res);

        }else{
            $this->qa_svc->messageOutput('200', array('list' => '', 'pages' => '', 'tags' => ''));
        }

    }


    public function getOneQaContentByQidAction(){

        $qid = $this->question_id;
//        var_dump($qid); die;
//        $qid = 2;

        $where = array(
            'q.id' => $qid,
            'q.main_status' => 5,
            'aa.status' => 1
        );

        $params_condition = array(
            'table' =>'qa_question q',
            'select' => 'q.id, q.content, aa.content as answer',
            'join' => array(
                array(
                    'type' => 'INNER',
                    'table' => 'qa_admin_answer aa',
                    'on' => 'q.id = aa.question_id',
                ),
            ),
            'where' => $where,
            'limit' => 1
        );

        // 查询输出结果 json 格式
        $res = $this->qa_svc->getByParams($params_condition);

        $this->qa_svc->messageOutput('200', $res);

    }


    private function getCateIdByPid($product_id){

        $params_condition = array(
            'table' =>'qa_question_product_rel qpr',
            'select' => 't.category_id',
            'join' => array(
                array(
                    'type' => 'INNER',
                    'table' => 'qa_question_tag_rel qtr',
                    'on' => 'qpr.question_id = qtr.question_id',
                ),
                array(
                    'type' => 'INNER',
                    'table' => 'qa_tag t',
                    'on' => 't.id = qtr.tag_id',
                ),
            ),
            'where' => array('qtr.tag_id'=>'>|5', 'qpr.product_id' => $product_id),
            'limit' => 1
        );

        $res = $this->qa_svc->getByParams($params_condition);

        if(is_array($res['list'])){
            return $res['list'][0]['category_id'];
        }else{
            return 0;
        }

    }

}