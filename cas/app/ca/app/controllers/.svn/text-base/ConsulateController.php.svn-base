<?php
use \Lvmama\Cas\Service\RedisDataService;
use \Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Utils\Filelogger;

class ConsulateController extends ControllerBase
{
    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $destin;

    /**
     * @var \Lvmama\Common\Utils\Filelogger
     */
    private $logger;

    private $table = 'ly_consulate';

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
        $fields = array();
        $values = array();
        $param = array();
        $return = true;
        $object_type = 'CONSULATE';
        if(!empty($post['table'])) $this->table = $post['table'];
        $ly_time_fields = array('dest_id','object_id','object_type','start_time','end_time','update_time','memo');
        if($this->table == 'ly_visa_consulate') $object_type = 'VISA_CONSULATE';
        try{
            $this->destin->beginTransaction();
            switch($post['action']){
                case 'seq'://保存排序
                    if(empty($post['seq']) || !is_array($post['seq'])){
                        $this->_errorResponse(10001, '请传入排序值');
                    }
                    foreach($post['seq'] as $consulate_id => $seq){
                        $param[] = array(':seq' => $seq,':consulate_id' => $consulate_id);
                    }
                    $sql = 'UPDATE `'.$this->table.'` SET `seq` = :seq WHERE `consulate_id` = :consulate_id';
                    $this->destin->execute($sql,$param,true);
                    break;
                case 'add'://自建添加
                    if(isset($post['info']['consulate_id'])) unset($post['info']['consulate_id']);
                    $dest_id = intval($post['info']['dest_id']);
                    foreach($post['info'] as $field => $value){
                        $fields[] = $field;
                        $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
                    }
                    $sql = 'INSERT INTO '.$this->table.'(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                    $this->destin->execute($sql,$values);
                    $consulate_id = $this->destin->lastInsertId();
                    //添加开放时间
                    foreach($post['times'] as $k => $row){
                        $param[$k][':dest_id'] = $dest_id;
                        $param[$k][':object_id'] = $consulate_id;
                        $param[$k][':object_type'] = $object_type;
                        $param[$k][':start_time'] = $row['start_time'];
                        $param[$k][':end_time'] = $row['end_time'];
                        $param[$k][':update_time'] = time();
                        $param[$k][':memo'] = $row['memo'];
                    }
                    $sql = 'INSERT INTO ly_time(`'.implode('`,`',$ly_time_fields).'`) VALUES(:'.implode(',:',$ly_time_fields).')';
                    $this->destin->execute($sql,$param,true);
                    break;
                case 'edit'://编辑
                    $consulate_id = intval($post['info']['consulate_id']);
                    $dest_id = intval($post['info']['dest_id']);
                    unset($post['info']['consulate_id']);
                    $values[':consulate_id'] = $consulate_id;
                    foreach($post['info'] as $field => $value){
                        $fields[] = '`'.$field.'` = :'.$field;
                        $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
                    }
                    $sql = 'UPDATE '.$this->table.' SET '.implode(',',$fields).' WHERE `consulate_id` = :consulate_id';
                    $this->destin->execute($sql,$values);
                    //删掉旧的
                    $sql = 'DELETE FROM ly_time WHERE object_type = :object_type AND dest_id = :dest_id AND object_id = :object_id';
                    $param = array(
                        ':object_type' => 'CONSULATE',
                        ':dest_id' => $dest_id,
                        ':object_id' => $consulate_id
                    );
                    $this->destin->execute($sql,$param);
                    //添加开放时间
                    $fields = array('dest_id','object_id','object_type','start_time','end_time','update_time','memo');
                    foreach($post['times'] as $k => $row){
                        $param[$k][':dest_id'] = $dest_id;
                        $param[$k][':object_id'] = $consulate_id;
                        $param[$k][':object_type'] = $object_type;
                        $param[$k][':start_time'] = $row['start_time'];
                        $param[$k][':end_time'] = $row['end_time'];
                        $param[$k][':update_time'] = time();
                        $param[$k][':memo'] = $row['memo'];
                    }
                    $sql = 'INSERT INTO ly_time(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                    $this->destin->execute($sql,$param,true);
                    break;
                case 'delete':
                    $param[':consulate_id'] = $post['consulate_id'];
                    $sql = 'DELETE FROM '.$this->table.' WHERE consulate_id = :consulate_id';
                    $this->destin->execute($sql,$param);
                    //删除ly_time
                    $sql = 'DELETE FROM ly_time WHERE object_type = :object_type AND dest_id = :dest_id AND object_id = :object_id';
                    $param = array(':object_type' => $object_type,':dest_id' => $post['dest_id'],':object_id' => $post['consulate_id']);
                    $this->destin->execute($sql,$param);
                    //删除ly_consulate_info
                    $param = array(':consulate_id' => $post['consulate_id'],':dest_id' => $post['dest_id']);
                    $sql = 'DELETE FROM ly_consulate_info WHERE consulate_id = :consulate_id AND dest_id = :dest_id';
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
        $data = $this->request->getPost();
        $fields = array();
        $values = array();
        if(empty($data['table'])){
            $this->table = 'ly_consulate_info';
        }else{
            $this->table = $data['table'];
        }
        if(isset($data['table'])) unset($data['table']);
        if(empty($data['info_id'])){//添加
            if(isset($data['info_id'])) unset($data['info_id']);
            foreach($data as $field => $value){
                $fields[] = $field;
                $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
            }
            $sql = 'INSERT INTO '.$this->table.'(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
            $return = $this->destin->execute($sql,$values);
        }else{
            $values[':info_id'] = $data['info_id'];
            unset($data['info_id']);
            foreach($data as $field => $value){
                $fields[] = '`'.$field.'` = :'.$field;
                $values[':'.$field] = is_numeric($value) ? intval($value) : $value;
            }
            $sql = 'UPDATE '.$this->table.' SET '.implode(',',$fields).' WHERE info_id = :info_id';
            $return = $this->destin->execute($sql,$values);
        }
        $this->_successResponse($return);
    }
}