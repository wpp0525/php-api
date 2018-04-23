<?php
use Lvmama\Common\Utils;
/**
 * PK目的地接口控制器
 * 
 * @author win.sx
 *
 */
class PkdestController extends PkController
{
    public function initialize()
    {
        $this->api = 'dest';
        parent::initialize();
    }

    public function getDestByIdAction($id = 10)
    {
        if (!$id || !is_numeric($id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $destination = $this->di->get('cas')->get('destination-data-service');
        $data = $destination->getDestById($id);
        $this->succResponse($data);
    }

    /**
     * 取得目的地下面图片数量
     * @param int $dest_id
     * @return int
     * @example curl -i -X GET http://ca.lvmama.com/pkdest/getImgCount/1/
     */
    public function getImgCountAction($dest_id = 0)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        $trip = $this->di->get('cas')->get('trip-data-service');
        $trace = $this->di->get('cas')->get('trace-data-service');
        $segment = $this->di->get('cas')->get('segment-data-service');
        $trip_ids = $trace->getTripIdsByDestId($dest_id);
        $segment_ids = $segment->getSegmentIds($trip_ids);
        $this->succResponse(count($segment_ids));
    }

    /**
     * 取得目的地下面条件的图片
     * @param int $dest_id
     * @param int $page
     * @param int $pageSize
     * @param int $uid
     * @return array
     * @example curl -i -X GET http://ca.lvmama.com/pkdest/getImgCount/1/
     */
    public function getImgAction($dest_id = 0,$page = 1,$pageSize = 15,$uid = 0)
    {
        if(!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        $trip = $this->di->get('cas')->get('trip-data-service');
		$trace = $this->di->get('cas')->get('trace-data-service');
        $segment = $this->di->get('cas')->get('segment-data-service');
        $praise = $this->di->get('cas')->get('praise-data-service');
        $spicture = $this->di->get('cas')->get('spicture-data-service');
        $comment = $this->di->get('cas')->get('comment-data-service');
        $trip_ids = $trace->getTripIdsByDestId($dest_id);
        $segment_list = $segment->getSegmentList($trip_ids);
        $segment_ids = $segment->getSegmentIds($trip_ids);
        $trip_list = $trip->getLists($trip_ids);
        $trip_list = Utils\UCommon::parseItem($trip_list,"trip_id");
        foreach($segment_list as $seg_k=>$seg_v){
            foreach($trip_list as $trip_k=>$trip_v){
                if($seg_v["trip_id"]==$trip_k){
                    $segment_list[$seg_k]["trip_title"]=$trip_v["title"];
                    break;
                }else{
                    $segment_list[$seg_k]["trip_title"]="";
                }
            }
        }
        $segment_list = Utils\UCommon::parseItem($segment_list,"segment_id");
        if($segment_ids){
            if(is_array($segment_ids)){
                $segmentIds= implode(',',$segment_ids);
            }
            $comments = $comment->getLists($segmentIds,$uid);
            $praises = $praise->getLists($segmentIds,$uid);
            $pics = $spicture->getLists($segmentIds,$page,$pageSize);
            $pic_temp = $pics["list"];
            foreach($pic_temp as $pic_k=>$pic_v){
                $pic_temp[$pic_k]["is_praise"]="N";
                $pic_temp[$pic_k]["is_comment"]="N";
                $pic_temp[$pic_k]["praiseCount"] = $praise->getCount("`channel`='trip' AND `object_type`='segment' AND `object_id`='{$pic_v["segment_id"]}'");
                $pic_temp[$pic_k]["commentCount"] = $comment->getCount("`channel`='trip' AND `object_type`='pic' AND `object_id`='{$pic_v["segment_id"]}' AND valid='Y'");
                foreach($segment_list as $seg_kk=>$seg_vv){
                    $pic_temp[$pic_k]["shareCount"]=$seg_vv["count_share"];
                    if($pic_v["segment_id"]==$seg_kk){
                        $pic_temp[$pic_k]["trip_title"]=$seg_vv["trip_title"];
                        $pic_temp[$pic_k]["trip_id"]=$seg_vv["trip_id"];
                        break;
                    }else{
                        $segment_list[$seg_k]["trip_title"]="";
                    }
                }
                foreach($comments as $com_v){
                    if($pic_v["segment_id"]==$com_v["object_id"]){
                        $pic_temp[$pic_k]["is_comment"]="Y";;
                    }
                }
                foreach($praises as $pra_v){
                    if($pic_v["segment_id"]==$pra_v["object_id"]){
                        $pic_temp[$pic_k]["is_praise"]="Y";
                    }
                }
            }
            $pics["list"]=$pic_temp;
            $this->succResponse($pics);
        }
        $this->succResponse(array());
    }

    /**
     * 取得指定目的地人工推荐的景点
     * @param int $dest_id
     * @param int $limit
     * @example curl -i -X GET http://ca.lvmama.com/pkdest/getRecommendViewspot/100/7/
     */
    public function getRecommendViewspotAction($dest_id = 0,$limit = 7)
    {
        if(!$dest_id || !is_numeric($dest_id)) {
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        if($limit < 0 || $limit > 15){
            $this->errResponse(100002, '一次获取条数须在1到15条之间');
        }
        $scenicviewspot = $this->di->get('cas')->get('scenicviewspot-data-service');
        $result = $scenicviewspot->getRecommendDestByDestid($dest_id,'VIEWSPOT','VIEWSPOT',$limit);
        $this->succResponse($result);
    }
}