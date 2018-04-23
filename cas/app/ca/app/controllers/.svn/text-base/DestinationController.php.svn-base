<?php
use \Lvmama\Cas\Service\RedisDataService;
use \Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Utils\Filelogger;

class DestinationController extends ControllerBase
{
    /**
     * @var Lvmama\Cas\Service\DestinationService
     */
    private $destination;

    public function initialize()
    {
        parent::initialize();
        $this->destination = $this->di->get('cas')->get('destination_service');
    }

    public function editAction()
    {
        $data = $this->request->get();
        $rs = $this->destination->edit($data['id'], json_decode($data['info'], true));

        $this->_successResponse($rs);
    }
    /**
     * 添加预设信息
     */
    public function addAction(){
        $data = $this->request->getPost();
        if(empty($data['table'])) $this->_errorResponse(10001,'please input table name');
        $table_name = $data['table'];
        if(empty($data['data']) || !is_array($data['data'])) $this->_errorResponse(10002,'please correct content,must array type');
        $fields = array();
        $values = array();
        foreach($data['data'] as $field => $value){
            $fields[] = $field;
            $values[':'.$field] = $value;
        }
        $sql = 'INSERT INTO '.$table_name.'(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
        try{
            $this->destination->execute($sql,$values);
        }catch (\Exception $e){
            Filelogger::getInstance()->addLog($e->getTraceAsString(),'ERROR');
            $this->_errorResponse($e->getCode(),$e->getMessage());
        }
        $this->_successResponse($this->destination->lastInsertId());
    }

}
