<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-3
 * Time: 下午2:07
 */
use Lvmama\Common\Utils;
use Lvmama\Cas\Service\QuestionDataService;
use Lvmama\Cas\Service\AnswerDataService;


class QaController extends ControllerBase {

    private $qa_svc;
    private $qa_tag_svc;
    private $qt_rel_svc;
    private $ans_svc;

    public function initialize() {
        parent::initialize();
        $this->qa_svc = $this->di->get('cas')->get('qaquestion-data-service');
        $this->qa_tag_svc = $this->di->get('cas')->get('qatag-data-service');
        $this->qt_rel_svc = $this->di->get('cas')->get('qaquestiontagrel-data-service');
        $this->ans_svc = $this->di->get('cas')->get('qaanswer-data-service');
    }

    public function addQuestionAction(){
        $data = array(
            'uid' => 'ceshishuju',
            'username' => 'ceshimingcheng',
            'title' => '为什么为什么',
            'content' => '为什么为什么？为什么？',
            'auditor_id' => '0',
            'auditor_time' => '0',
            'status' => '0',
            'create_time' => time(),
            'update_time' => time(),
        );
        $res = $this->qa_svc->operateQuestion($data);

        echo json_encode($res);
    }

    public function updateQuestionAction(){
        $data = array(
            'uid' => 'ceshishuju123',
            'username' => 'ceshimingcheng123',
            'title' => '为什么为什么????',
            'content' => '为什么为什么？为什么？????',
            'auditor_id' => '0',
            'auditor_time' => '0',
            'status' => '0',
            'update_time' => time(),
        );
        $res = $this->qa_svc->operateQuestion($data, 5);
        echo json_encode($res);
    }

    public function testAction(){
        $a=$this->qt_rel_svc->getQidByTid(1, 'all');
        var_dump($a);
    }


}