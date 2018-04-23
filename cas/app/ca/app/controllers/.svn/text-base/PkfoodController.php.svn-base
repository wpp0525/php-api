<?php
use Lvmama\Common\Utils;
/**
 * PK目的地接口控制器
 * 
 * @author win.sx
 *
 */
class PkfoodController extends PkController
{
    public function initialize()
    {
        $this->api = 'food';
        parent::initialize();
    }

    /**
     * 取得目的地下面美食数量
     * @param int $dest_id
     * @return int
     * @example curl -i -X GET http://ca.lvmama.com/pkdest/getFoodCount/1/
     */
    public function getFoodCountAction($dest_id = 0)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        $destination = $this->di->get('cas')->get('destination-data-service');
        $food = $this->di->get('cas')->get('food-data-service');
        $dest = $destination->getDestById($dest_id);
        if(!$dest){
            $this->errResponse(100002, '没有找到相关的目的地信息');
        }
        $total = $food->getDestFoodNum($dest);
        $this->succResponse($total);
    }

    /**
     * 取得目的地下面相关美食
     * @param int $dest_id
     * @param int $page
     * @param int $pageSize
     * @param int $uid
     * @return array
     * @example curl -i -X GET http://ca.lvmama.com/pkdest/getImgCount/1/
     */
    public function getFoodAction($dest_id = 0,$page = 1,$pageSize = 15)
    {
        if(!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
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
        $food = $this->di->get('cas')->get('food-data-service');
        $total = $food->getDestFoodNum($dest);
        if($total){
            $totalPage = ceil($total / $pageSize);
            $page = $page < 1 ? 1 : $page;
            $page = $page > $totalPage ? $totalPage : $page;
            $result = $food->getRecommendFood($dest_id,array('page' => $page,'pageSize' => $pageSize));
            if($result && count($result) < $pageSize){
                $foodIds = Utils\UCommon::parseId($result,'food_id');
                $result = array_merge($result,$food->getDestHaveFood($dest,array('page' => $page,'pageSize' => $pageSize - count($result)),$foodIds));
            }else{
                $result = $food->getDestHaveFood($dest,array('page' => $page,'pageSize' => $pageSize));
            }
            //有图片的放前面
            $len = count($result);
            for($i = 0;$i < $len - 1;$i++){
                for($j = $i+1;$j < $len;$j++){
                    if($result[$j]['have_img']){
                        $tmp = $result[$j];
                        $result[$j] = $result[$i];
                        $result[$i] = $tmp;
                    }
                }
            }
            $this->succResponse($result);
        }
        $this->succResponse(array());
    }
}