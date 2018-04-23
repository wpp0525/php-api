<?php
use Lvmama\Common\Utils;
/**
 * PK统计接口控制器
 * 
 * @author win.sx
 *
 */
class PkhotController extends PkController
{
    public function initialize()
    {
        $this->api = 'hot';
        parent::initialize();
    }

    /**
     * 取得指定类型目的地的热门PK
     * @param string $dest_type
     * @param int $page
     * @param int $pageSize
     * @example curl -i -X GET http://ca.lvmama.com/pkhot/getHot/
     */
    public function getHotAction($dest_type = 'CITY',$page = 1,$pageSize = 5){
        if(!in_array($dest_type,$this->_type)){
            $this->errResponse(100001, '请传入有效的目的地类型');
        }
        $pkcount = $this->di->get('cas')->get('pkcount-data-service');
        $result = $pkcount->getHotList($dest_type,array('page' => $page,'pageSize' => $pageSize));
        $this->succResponse($result);
    }
    /**
     * 取得目的地下面相关热门PK
     * @param int $dest_id
     * @param int $page
     * @param int $pageSize
     * @return array
     * @example curl -i -X GET http://ca.lvmama.com/pkhot/getHotByDestId/1/
     */
    public function getHotByDestIdAction($dest_id = 0,$page = 1,$pageSize = 15)
    {
        if(!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        if($pageSize < 1 || $pageSize > 15){
            $this->errResponse(100002, '每次最多只能取15条');
        }
        $destination = $this->di->get('cas')->get('destination-data-service');
        $dest = $destination->getDestById($dest_id);
        if(!$dest){
            $this->errResponse(100003, '没有找到相关的目的地信息');
        }
        if(!isset($dest['dest_type']) || !in_array($dest['dest_type'],$this->_type)){
            $this->errResponse(100004, '目的地类型有误!');
        }
        $pkcount = $this->di->get('cas')->get('pkcount-data-service');
        $total = $pkcount->getCountByDestId($dest['dest_id']);
        if($total){
            $totalPage = ceil($total / $pageSize);
            $page = $page < 1 ? 1 : $page;
            $page = $page > $totalPage ? $totalPage : $page;
            $result = $pkcount->getLists($dest_id,array('page' => $page,'pageSize' => $pageSize));
            $result['count'] = $total;
            $this->succResponse($result);
        }
        $this->succResponse(array());
    }
}