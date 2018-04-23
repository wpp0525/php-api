<?php

use \Lvmama\Cas\Service\RedisDataService;
use \Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Utils\Filelogger;

class VisaController extends ControllerBase
{
    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $destin;

    /**
     * @var \Lvmama\Common\Utils\Filelogger
     */
    private $logger;

    private $table = 'ly_visa';

    private $primary_key = 'visa_id';

    public function initialize()
    {
        parent::initialize();
        $this->destin = $this->di->get('cas')->get('destin_base_service');
        $this->logger = Filelogger::getInstance();
    }
    /**
     * 保存
     */
    public function saveAction(){
        $post = $this->request->getPost();
        //自建或者编辑
        $post['action'] = isset($post['action']) ? $post['action'] : '';
        $post['page_type'] = isset($post['page_type']) ? $post['page_type'] : '';
        $fields = array();
        $values = array();
        $param = array();
        $return = true;
        switch($post['page_type']){
            case 'communication':
                $this->table = 'ly_communication';
                $this->primary_key = 'communication_id';
                break;
            case 'visa_consulate':
                $this->table = 'ly_visa_consulate';
                $this->primary_key = 'consulate_id';
                break;
            case 'facility':
                $this->table = 'ly_facility';
                $this->primary_key = 'facility_id';
                break;
            case 'main_dest':
            case 'scenic':
            case 'restaurant':
            case 'shop':
            case 'playspot':
                $this->table = 'ly_scenic_viewspot';
                $this->primary_key = 'recommend_id';
                break;
            case 'food':
                $this->table = 'ly_food_recommend';
                $this->primary_key = 'recommend_id';
                break;
            case 'goods':
                $this->table = 'ly_goods_recommend';
                $this->primary_key = 'recommend_id';
                break;
            case 'play':
                $this->table = 'ly_play_type';
                $this->primary_key = 'play_type_id';
                break;
            case 'stay':
                $this->table = 'ly_stay_type';
                $this->primary_key = 'stay_type_id';
                break;
            case 'stay_dest':
                $this->table = 'ly_stay_dest';
                $this->primary_key = 'rel_id';
                break;
            case 'substay':
                $this->table = 'ly_stay';
                $this->primary_key = 'stay_id';
                break;
            case 'travel':
                $this->table = 'ly_travel';
                $this->primary_key = 'travel_id';
                break;
            case 'travel_day':
                $this->table = 'ly_travel_day';
                $this->primary_key = 'travel_day_id';
                break;
        }
        try{
            $this->destin->beginTransaction();
            switch($post['action']){
                case 'seq'://保存排序
                    if(empty($post['seq']) || !is_array($post['seq'])){
                        $this->_errorResponse(10001, '请传入排序值');
                    }
                    foreach($post['seq'] as $id => $seq){
                        $param[] = array(':seq' => $seq,':'.$this->primary_key => $id);
                    }
                    $sql = 'UPDATE `'.$this->table.'` SET `seq` = :seq WHERE `'.$this->primary_key.'` = :'.$this->primary_key;
                    $this->destin->execute($sql,$param,true);
                    break;
                case 'add'://自建添加
                    if(isset($post['info'][$this->primary_key])) unset($post['info'][$this->primary_key]);
                    foreach($post['info'] as $field => $value){
                        $fields[] = $field;
                        $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
                    }
                    $sql = 'INSERT INTO '.$this->table.'(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                    $this->destin->execute($sql,$values);
                    break;
                case 'edit'://编辑
                    $id = intval($post['info'][$this->primary_key]);
                    unset($post['info'][$this->primary_key]);
                    $values[':'.$this->primary_key] = $id;
                    foreach($post['info'] as $field => $value){
                        $fields[] = '`'.$field.'` = :'.$field;
                        $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
                    }
                    $sql = 'UPDATE '.$this->table.' SET '.implode(',',$fields).' WHERE `'.$this->primary_key.'` = :'.$this->primary_key;
                    $this->destin->execute($sql,$values);
                    break;
                case 'delete':
                    $param[':'.$this->primary_key] = $post[$this->primary_key];
                    $sql = 'DELETE FROM '.$this->table.' WHERE '.$this->primary_key.' = :'.$this->primary_key;
                    $this->destin->execute($sql,$param);
                    break;
                default:
                    //暂不处理
            }
            $this->destin->commit();
        }catch (\Exception $e){
            $this->destin->rollBack();
            $this->_errorResponse($e->getCode(),$e->getMessage());
        }
        $this->_successResponse($return);
    }

    /**
     * 保存使馆信息
     */
    public function infoSaveAction(){

    }

    public function consulateSaveAction(){

    }

	public function setDefaultAction(){
        $dest_id = $this->request->get('dest_id');
        $page_type = $this->request->get('page_type');
        if(empty($dest_id) || !is_numeric($dest_id)) $this->_errorResponse(10001, 'please input dest_id and must is number!');
        $param = array();
        switch($page_type){
            case 'inout'://出入境办理
                $preset = array(
                    '海关',
                    '入境卡',
                    '出境卡',
                    '出入境贴士'
                );
                $type = 'CRJ';
                $fields = array('dest_id','memo','type','preseted','visa_name');
                foreach($preset as $v){
                    $param[] = array(
                        ':dest_id' => $dest_id,
                        ':memo' => '',
                        ':type' => $type,
                        ':preseted' => 'Y',
                        ':visa_name' => $v
                    );
                }
                break;
            case 'communication'://目的地指南-实用信息-通讯
                $this->table = 'ly_communication';
                $preset = array(
                    '手机电话卡',
                    '网络'
                );
                $fields = array('dest_id','memo','status','preseted','seq','communication_name');
                foreach($preset as $v){
                    $param[] = array(
                        ':dest_id' => $dest_id,
                        ':memo' => '',
                        ':status' => 99,
                        ':preseted' => 'Y',
                        ':seq' => 0,
                        ':communication_name' => $v
                    );
                }
                break;
            case 'scenic_summary':
                $cate_id = $this->request->get('cate_id');
                if(empty($cate_id) || !is_numeric($cate_id)) $this->_errorResponse(10002, 'please input cate_id and must is number!');
                $this->table = 'ly_data';
                $preset = array('景点概述');
                $fields = array('text','status','dest_id','cate_id','data_type','parent_id','parents','url','preseted','title');
                foreach($preset as $v){
                    $param[] = array(
                        ':status' => 99,
                        ':dest_id' => $dest_id,
                        ':cate_id' => $cate_id,
                        ':data_type' => 'TEXT',
                        ':parent_id' => 0,
                        ':parents' => 0,
                        ':url' => '',
                        ':preseted' => 'Y',
                        ':title' => $v
                    );
                }
                break;
            case 'food_summary':
                break;
            case 'shopping_summary':
                break;
            case 'play_summary':
                break;
            default://签证办理
                $preset = array(
                    '签证总介绍',
                    '签证流程',
                    '签证材料',
                    '签证办理时间',
                    '签证费用',
                    '使用情况',
                    '免签条件'
                );
                $type = 'QZ';
                $fields = array('dest_id','memo','type','preseted','visa_name');
                foreach($preset as $v){
                    $param[] = array(
                        ':dest_id' => $dest_id,
                        ':memo' => '',
                        ':type' => $type,
                        ':preseted' => 'Y',
                        ':visa_name' => $v
                    );
                }
        }
        $sql = 'INSERT INTO '.$this->table.'(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
        $this->_successResponse($this->destin->execute($sql,$param,true));
	}
}