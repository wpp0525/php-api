<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * Redis 服务类
 *
 * @author mac.zhao
 *
 */
class RedisDataService extends DataServiceBase {

	const REDIS_AUDIT_TRIPID = 'robot:tripid:audit:';

	const REDIS_EDIT_TRIPID = 'robot:tripid:edit';

	const REDIS_ROBOT_TIMER = 'robot:timer';

	const REDIS_ROBOT_COUNTER = 'robot:counter';

	const REDIS_DEST_BASE_DESTID ='dest:base_info:dest_id:';

	const REDIS_DEST_PARENTS_DESTID = 'dest:parents:dest_id:';

	const REDIS_DEST_DETAIL_BASEID ='dest:detail:base_id:';

	const REDIS_RECOM_DEST_IDS='recom:dest:ids:dest_id:';

	const REDIS_DEST_PARENTS   = 'dest:parents:dest_id:';

	const REDIS_DEST_TRAVEL  ="dest:travel:dest_id: ";

    const REDIS_TRAVEL_VIEWNUM ='travel:viewnum:travel_id:';

	const REDIS_TRAVEL_VIEWIDS ='travel:viewids:travel_id:';

	const REDIS_API_DATA   ="API:DATA:URL:";

	const REDIS_TRIP_HASH  ='TRIP:LIST:HASH:';

	const REDIS_DEST_CHILD_LIST ='dest:child:list:dest_id:';

	const REDIS_OBJECT_SUBJECT_LIST='subject:object_id:';

	const REDIS_DEST_VIEWSPOT_LIST='dest:viewpsot:base_id:list:';

	const REDIS_DEST_VIEWSPOT_NEW_LIST='dest:viewpsotnew:dest_ids:list:';

	const REDIS_SUBJECT_DEST_LIST ='subject:dest:subject_id:list';

    const REDIS_DEST_SCENERY_SUMMARY='dest:scenery:summary:';

    const REDIS_DEST_VST_URL_PINYIN='dest:url_pinyin:';

	const REDIS_DEST_REST_LIST = 'dest:restaurant:base_id:list:';//目的地餐厅ID集合

	// 按照热度公式计算出的 审核通过 的问题的id 有序集合 question_id => 热度！ 热度！ 热度！
	// 热度 = 浏览数 + 回答数 * 10 + 小编推荐（100）；
	const REDIS_DEST_TRIP_IDS = 'dest:trip:ids:';

	/**
	 * 问题基础信息
	 *
	 * @var unknown
	 *
	 * @author mac.zhao
	 */
	const REDIS_QA_QUESTION_INFO ='qa:question:{id}';

    // ==========================
    // 记录 问题 question_id 与 标签 tag_id 的关系
    const REDIS_QA_QUESTION_TAGS = 'qa:question:{id}:tags';
    // 命名 REDIS_QA_PRODUCT_ 开头表示产品问答的key
    // tag 记录 产品product_id 与 标签tag_id 关系（不适用于 常见问题）
    // cate 记录 产品product_id 与 分类cate_id 关系 （数据不包括此分类下的 常见问题标签 内容）
    // bu 记录 BU 与 标签tag_id 关系（只适用于 常见问题，tag_id 必须为对应的常见问题的id）
    const REDIS_QA_PRODUCT_TAG_REL = 'qa:pro_rel:tag:{tag_id}_{product_id}';
    const REDIS_QA_PRODUCT_CATE_REL = 'qa:pro_rel:cate:{cate_id}_{product_id}';
    const REDIS_QA_PRODUCT_BU_REL = 'qa:pro_rel:bu:{bu_id}_{tag_id}';
    // 命名 REDIS_QA_COMMUNITY_ 开头表示产品问答的key
    // answer的基本信息等
    const REDIS_QA_COMMUNITY_ANSWER = 'qa:answer:{id}';
    // comment的基本信息等
    const REDIS_QA_COMMUNITY_COMMENT= 'qa:comment:{id}';
    // 问题下面 审核通过 的回答的id 有序集合 answer_id => update_time
    const REDIS_QA_COMMUNITY_QUESTION_ANSWER = 'qa:question:{id}:answer';
    // 有序集合 通过审核的问题的 有序集合 answer_id => valid_comment
    const REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT = 'qa:question:{id}:answer_hot';
    // 回答下面 审核通过 的评论的id 有序集合 comment_id => update_time
    const REDIS_QA_COMMUNITY_ANSWER_COMMENT = 'qa:answer:{id}:comment';

    // 按照热度公式计算出的 审核通过 的问题的id 有序集合 question_id => 热度！ 热度！ 热度！
    // 热度 = 浏览数 + 回答数 * 10 + 小编推荐（100）；
    const REDIS_QA_COMMUNITY_DEST_HOT = 'qa:com_rel:dest:{dest_id}_hot';
    // 关联目的地下 审核通过 的问题的id 有序集合 question_id => update_time
    const REDIS_QA_COMMUNITY_DEST_REL = 'qa:com_rel:dest:{dest_id}';
    // 有效回答（通过审核的回答数）等于零的 审核通过 的问题的id 有序集合 question_id => update_time
    // 有效回答大于0时 需删除！！！！
    const REDIS_QA_COMMUNITY_DEST_NOANSWER = 'qa:com_rel:dest:{dest_id}_zero';

    const REDIS_QA_COMMUNITY_TAG_HOT = 'qa:com_rel:tag:{tag_id}_hot';
    const REDIS_QA_COMMUNITY_TAG_REL = 'qa:com_rel:tag:{tag_id}';
    const REDIS_QA_COMMUNITY_TAG_NOANSWER = 'qa:com_rel:tag:{tag_id}_zero';

    const REDIS_QA_COMMUNITY_ALL_HOT = 'qa:com_rel:all_hot';
    const REDIS_QA_COMMUNITY_ALL_REL = 'qa:com_rel:all';
    const REDIS_QA_COMMUNITY_ALL_NOANSWER = 'qa:com_rel:all_zero';

//    const REDIS_QA_COMMUNITY_USER = 'qa:user:{uid}';
    const REDIS_QA_COMMUNITY_USER_QUESTION = 'qa:user:{uid}:question';
    const REDIS_QA_COMMUNITY_USER_ANSWER_ID = 'qa:user:{uid}:answer_ids';
    const REDIS_QA_COMMUNITY_USER_ANSWER_QID = 'qa:user:{uid}:answer_qids';
    const REDIS_QA_COMMUNITY_USER_FOLLOW = 'qa:user:{uid}:follow';

    const REDIS_QA_COMMUNITY_QUESTION_PV = 'qa:question:{id}:pv';


    const REDIS_QA_COMMUNITY_ANSWER_TOP5 = 'qa:question:user_answer_top5';

    const REDIS_QA_COMMUNITY_IMPORT_USER1 = 'qa:update_temp:user_list1';
    const REDIS_QA_COMMUNITY_IMPORT_USER2 = 'qa:update_temp:user_list2';
    const REDIS_QA_COMMUNITY_IMPORT_CONTENT = 'qa:update_temp:question_answer_tag';

    const REDIS_QA_COMMUNITY_IMPORT_CONFIG = 'qa:update_temp:config';
    const REDIS_QA_COMMUNITY_IMPORT_RESLOG = 'qa:update_temp:result_log';

    // 标签信息
    const  REDIS_QA_COMMUNITY_TAG = 'qa:tag:{tag_id}';
    // ==========================

	const REDIS_CORE_ADMIN_REALNAME ='core:admin:realname:';

	const REDIS_JAVA_PRODUCT_INFO ='java:product:info:';

    /**************************  目的地改版 php-dest  begin  ***************************/
    const REDIS_NEW_DEST_ALL_INFO ='new_dest:new_all_info:dest_id:{dest_id}';
    const REDIS_NEW_DEST_HOT_BROTHER = "new_dest:hot_brother:{dest_id}_{parent_id}_{limit}";

    // tdk
    const REDIS_NEW_DEST_TDK_KEY ='new_dest:tdk_key:{tdk_key}';
    const REDIS_NEW_DEST_TDK_DEST = "new_dest:tdk_dest:{dest_id}";

    // seo

    const REDIS_NEW_HOT_DEST_RECOM_SEASON = "new_dest:seo_hotdest:recom_season:";
    /**************************  目的地改版 php-dest  end  ***************************/



	/**
	 * 游记基础信息
	 *
	 * @var unknown
	 *
	 * @author mac.zhao
	 */
	const REDIS_TRAVEL_INFO ='tr:travel:{id}';

	/**
	 * 游记列表数据
	 */
	const REDIS_TRAVEL_LIST_DATA = 'tr:travel:{travel_id}:list';

	/**
	 * 游记内容信息
	 *
	 * @var unknown
	 *
	 * @author mac.zhao
	 */
	const REDIS_TRAVEL_CONTENT ='tr:travel:{travelid}:content:{id}';

	/**
	 * 游记:内容:推荐目的地信息
	 *
	 * @var zset
	 *
	 * @author mac.zhao
	 */
	const REDIS_TRAVEL_CONTENT_RECOMMEND_DEST ='tr:travel:{travelid}:content:{id}:recommend-dest';

	const REDIS_QA_QUESTIONIDS_UID = 'qa:questionids:uid:';

	const REDIS_QA_QUESTIONIDS_HAVEANSWER_UID = 'qa:questionids:haveanswer:uid:';

	const REDIS_QA_SQL = 'qa:question:sql:';
	/**
	 * QA中可用的分类
	 * @author shenxiang
	 */
	const REDIS_QA_CATE = 'qa:question:cate:';
	/**
	 * QA中属于某分类下的可用的标签
	 * @author shenxiang
	 */
	const REDIS_QA_CATE_TAG = 'qa:question:cate:tag:';
	/**
	 * QA中跟产品相关的问题
	 * @author shenxiang
	 */
	const REDIS_QA_TAG_REL = 'qa:question:tag:rel:';
	/**
	 * QA中管理员的回答
	 * @author shenxiang
	 */
	const REDIS_QA_ADMIN_ANSWER = 'qa:question:admin:answer:';
	/**
	 * 根据dest_id取得指南信息
	 * @author shenxiang
	 */
	const REDIS_DEST_SUMMARY = 'dest:summary:dest_id:';
	/**
	 * 根据dest_id取得地址信息
	 * @author shenxiang
	 */
	const REDIS_DEST_ADDRESS = 'dest:address:{dest_id}';
	/**
	 * 根据dest_id取得推荐信息
	 * @author shenxiang
	 */
	const REDIS_DEST_RECOMMEND = 'dest:recommend:{dest_id}';
	/**
	 * 根据dest_id取得图片信息
	 * @author shenxiang
	 */
	const REDIS_DEST_PICS = 'dest:pics:dest_id:';
	/**
	 * 根据baseid取得dest信息
	 * @author shenxiang
	 */
	const REDIS_DEST_BASEID = 'dest:dest:baseid:';
	/**
	 * 根据dest_id取得base信息
	 * @author shenxiang
	 */
	const REDIS_BASE_DESTID = 'dest:base:destid:';
	/**
	 * 根据dest_id取得ly_destination表的指定记录
	 * @author shenxiang
	 */
	const REDIS_DEST_INFO = 'dest:destinfo:{id}';
	/**
	 * ly_dest_type列表
	 * @author shenxiang
	 * @type ZSET
	 */
	const REDIS_DEST_TYPE_LIST = 'dest:dest_type:all';
	/**
	 * ly_dest_type数据
	 * @author shenxiang
	 */
	const REDIS_DEST_TYPE_CODE = 'dest:desttype:code:';

	/**
	 * 根据district_id取得lmm_vst_destination.biz_district表的指定记录
	 * @author shenxiang
	 */
	const REDIS_DISTRICT_INFO = 'dest:district:{id}';
	/**
	 * 根据dest_ids取得ly_destination表的指定记录
	 * @author gaochunzheng
	 */
	const REDIS_DEST_LIST_INFO = 'dest:destlistinfo:{ids}';
	/**
	 * cms通用配置(mo_configure)
	 * @author shenxiang
	 */
	const REDIS_CONFIGURE_KEY = 'dest:configure:{type}';
	/**
	 * 根据dest_id取得lmm_vst_destination.biz_dest_multi_relation表的指定记录
	 * @author libiying
	 */
	const REDIS_MULTI_RELATION_KEY = 'dest:multirelation:{dest_id}';
	/**
	 * 外部接口数据缓存
	 * @author shenxiang
	 */
	const REDIS_EXTERNAL_API_KEY = 'external:api:';
	/**
	 * SEO分类
	 * @author shenxiang
	 */
	const REDIS_SEO_CATEGORY = 'seo:category:';
	/**
	 * Seo度假内链取C库数据缓存
	 * @author shenxiang
	 */
	const REDIS_SEO_C_KEY = 'seo:c:';
	/**
	 * Seo度假内链取D库数据缓存
	 * @author shenxiang
	 */
	const REDIS_SEO_D_KEY = 'seo:d:';
	/**
	 * ES通过目的地名称取得相应ID
	 * @author shenxiang
	 */
	const REDIS_ES_IDBYNAME = 'es:getIdByName:';
	/**
	 * ES通过目的地名称集合取得相应基本信息
	 * @author shenxiang
	 */
	const REDIS_ES_BASEINFO_NAMES = 'es:getIdsByNames:';
	/**
	 * ES搜索指定索引下指定类型中的关键字
	 * @author shenxiang
	 */
	const REDIS_ES_WAY_SEARCH = 'es:getWaySearch:';

	/**
	 * 根据food_id取得美食基本信息
	 * @author shenxiang
	 */
	const REDIS_FOOD_INFO = 'dest:food:{id}';
	/**
	 * 属于目的地下的美食数量
	 * @author shenxiang
	 */
	const REDIS_DEST_FOOD_NUM = 'dest:food:num:{sql}';
	/**
	 * 属于目的地下的美食
	 * @author shenxiang
	 */
	const REDIS_DEST_FOOD_DATA = 'dest:food:data:{sql}';
	/**
	 * 属于目的地下的美食概述
	 * @author shenxiang
	 */
	const REDIS_DEST_FOOD_SUMMARY = 'dest:food:summary:{dest_id}';
	/**
	 * 属于目的地下的美食主题
	 * @author shenxiang
	 */
	const REDIS_DEST_FOOD_THEME = 'dest:food:theme:{dest_id}';
	/**
	 * 根据美食取相关的餐厅
	 * @author shenxiang
	 */
	const REDIS_DEST_RESTAURANT_OF_FOOD = 'dest:restaurant:of:food:{sql}';
	/**
	 * 属于目的地下的推荐美食
	 * @author shenxiang
	 */
	const REDIS_DEST_RECOMMEND_FOOD = 'dest:food:recommend:{sql}';
	/**
	 * 属于目的地下的指定推荐类型的目的地信息
	 * @author shenxiang
	 */
	const REDIS_DEST_RECOMMEND_DATA = 'dest:recommend:data:{sql}';
	/**
	 * 根据目的地类型及父级目的地获取下级目的地集合
	 * @author shenxiang
	 */
	const REDIS_DEST_DATA_PID = 'dest:data:pid:{sql}';
	/**
	 * 指定频道和类型下的标签名称
	 * @author shenxiang
	 */
	const REDIS_MODULE_SUBJECT_RELATION_NAME = 'module:subject:relation:{sql}';

	/**
	 * 游记图片上传HASH
	 */
	const REDIS_TRAVEL_UPLOAD_IMAGE_LIST = 'travel:uploadimage:list:{image_id}';

	/**
	 * 游记浏览数
	 */
	const REDIS_TRAVEL_VIEW_NUM = 'trip:viewnum:trip_id:{travel_id}';

	/**
	 * 专题lp后台数据 HASH
	 */
	const REDIS_SUBJECT_LP_LIST = 'subject:lp:{block_id}';

	/**
	 * 大目的地 线路接口数据
	 * @author shenxiang
	 */
	const  REDIS_SEO_ROUTER_DATA = 'seo:router:{params}';
	/**
	 * 大目的地 门票接口数据
	 * @author shenxiang
	 */
	const  REDIS_SEO_TICKET_DATA = 'seo:ticket:{params}';
	/**
	 * 大目的地 酒店接口数据
	 * @author shenxiang
	 */
	const  REDIS_SEO_HOTEL_DATA = 'seo:hotel:{params}';
	/**
	 * 大目的地 TDK
	 */
	const REDIS_SEO_TDK_DATA = 'seo:tdk:{params}';

	/**
	 * 大目的地二期 TDK
	 */
	const REDIS_SEO_TDK_DATA_NEW = 'seo:tdk2:{params}';

	/**
	 * 大目的地 无搜索版公共头部
	 */
	const REDIS_SEO_NOSEARCH_HEADER = 'seo:nosearch:header';
	/**
	 * 大目的地 产品URL
	 */
	const REDIS_SEO_PRODUCT_URL = 'seo:product:url:{params}';
	/**
	 * 大目的地 热门目的地
	 */
	const REDIS_SEO_HOT_DEST = 'seo:hot:dest:{params}';
	/**
	 * 大目的地 游记攻略
	 */
	const REDIS_SEO_TRIP = 'seo:trip:{params}';

	/**
	 * 大目的地页面参数
	 */
	const REDIS_SEO_STATIC_DESTVARS = 'seo:destvars:{keyword_id}';
	/**
	 * 线路类型筛选项
	 */
	const REDIS_SEO_ROUTER_FILTER = 'seo:route:filters:{filter_type}';
	/**
	 * 门票类型筛选项
	 */
	const REDIS_SEO_TICKET_FILTER = 'seo:ticket:filters:{filter_type}';
	/**
	 * 酒店类型筛选项
	 */
	const REDIS_SEO_HOTEL_FILTER = 'seo:hotel:filters:{filter_type}';

	/**
     * 站内信组类型
     */
    const REDIS_MSG_GROUP_CODE = 'msg:group:list';

    /**
     * 站内信组类型对应CODE
     */
    const REDIS_MSG_GROUP_CODE_ARR = 'msg:group:arr';

    /**
     * 站内信消息类型对应组
     */
    const REDIS_MSG_TYPE_GROUP_ARR = 'msg:group:type';

    /**
     * 专题SCT后台数据
     */
     const REDIS_SUBJECT_SCT_LIST = 'subject:sct:{block_id}';

    /**
     * 周边酒店--距离
     */
    const REDIS_AROUND_HOTEL_DISTANCE = 'around_product:hotel_distance_';

    /**
     * 周边酒店--评价
     */
    const REDIS_AROUND_HOTEL_EVALUATION = 'around_product:hotel_evaluation_';

    /**
     * 周边酒店--人气
     */
    const REDIS_AROUND_HOTEL_POPULARITY = 'around_product:hotel_type_';

    /**
     * 周边景点--距离
     */
    const REDIS_AROUND_VIEWSPOT_DISTANCE = 'around_product:viewspot_distance_';

    /**
     * 周边景点--评价
     */
    const REDIS_AROUND_VIEWSPOT_EVALUATION = 'around_product:viewspot_evaluation_';

    /**
     * 周边景点--人气
     */
    const REDIS_AROUND_VIESPOT_POPULARITY = 'around_product:viewspot_popularity_';

    /**
     * 热门推荐
     */
    const REDIS_RECOMMEND_POPULARITY = 'around_product:recommend_popularity_';

    /**
     * 首屏产品--非门票
     */
    const REDIS_FIRST_SCREEN_PRODUCTS = 'around_product:first_screen_products_';

    /**
     * 首屏产品--门票
     */
    const REDIS_RECOMMEND_TICKET_GOODS = 'around_product:recommend_ticket_goods';

    /**
     * 热门推荐 OTHER
     */
    const REDIS_RECOMMEND_POPULARITY_OTHER = 'around_product:recommend_popularity_other_';
    const REDIS_RECOMMEND_POPULARITY_RPOI = 'around_product:recommend_popularity_rpoi_';

    /** 百度搜索 */
	//关键词id，losc映射 （使用时{keywordId}替换掉）
	const REDIS_BAIDUSEARCH_KEYWORD_LOSC = 'baidusearch:keyword:losc:{keywordId}';
	const REDIS_BAIDUSEARCH_LOSC_KEYWORD = 'baidusearch:losc:keyword:{losc}';
	//losc，订单聚合 （使用时{loscId}替换掉），device:pc/mobile
//	const REDIS_BAIDUSEARCH_LOSC_ORDER = 'baidusearch:losc:order:{device}:{loscId}:{Y-i-d H}';

	const REDIS_DEST_INDEX_IMAGE_NEW_LIST = 'dest:indeximage:dest_ids:list:';
	const REDIS_POIS_IMAGE_LIST = 'dest:pois:images:{num}:{pois}';

	const REDIS_ENV_LIST = 'envconfig:';
	
	/**
	 *  产品池V2
	 * @author shenxiang
	 */
	const REDIS_PRODUCT_POOL_V2 = 'productpoolv2:';
	const REDIS_PRODUCT_POOL_V2_ADDITION = 'productpoolv2:addition:';
	const REDIS_GOODS_POOL_V2 = 'goodspoolv2:';
	const REDIS_GOODSLIB_CATEGORY = 'goodslib:category:';
	/**
	 * 产品与所在城市行政区ID对应关系
	 * @author shenxiang
	 */
	const REDIS_PRODUCT_CITY_DISTRICT_ID = 'productpoolv2:product:city:district_id';

	/** 过期时间 **/
	const REDIS_EXPIRE_ONE_MINUTE = 60;//1分钟
	const REDIS_EXPIRE_QUARTER_HOUR = 900;//1刻钟
	const REDIS_EXPIRE_HALF_HOUR = 1800;//半小时
	const REDIS_EXPIRE_ONE_HOUR = 3600;//1小时
	const REDIS_EXPIRE_HALF_DAY = 43200;//半天
	const REDIS_EXPIRE_ONE_DAY = 86400;//1天
	const REDIS_EXPIRE_HALF_MONTH = 1296000;//半个月(以15天计算)
	const REDIS_EXPIRE_ONE_MONTH = 2592000;//1个月(以30天计算)

	/**
	 * 通用set操作
	 * @param $redis_key
	 * @param $data
	 * @param $ttl
	 */
	public function dataSet($redis_key,$data,$ttl){
		$this->redis->set($redis_key,$data);
		if($ttl){
			$this->redis->setex($redis_key,$ttl,$data);
		}
	}

	/**
	 * 通用get操作
	 * @param $redis_key
	 * @return mixed
	 */
	public function dataGet($redis_key){
		return $this->redis->get($redis_key);
	}

	/**
	 * 通用获取hash全表操作
	 * @param $redis_key
	 * @return mixed
	 */
	public function dataHgetall($redis_key){
		return $this->redis->hgetall($redis_key);
	}
	/**
	 * 一次性插入多个结果到hash(通用)
	 * @param $redis_key
	 * @param $data
	 * @param $ttl
	 */
	public function dataHmset($redis_key,$data,$ttl){
		$this->redis->hmset($redis_key,$data);
		if($ttl){
			$this->redis->expire($redis_key,$ttl);
		}
	}

    public function dataHmget($redis_key, $key_array){
        return $this->redis->hmget($redis_key, $key_array);
    }

	/**
	 * 存储某个目的地下推荐目的地数据
	 * @param $redis_key
	 * @param $data  if 二维数组，则必须有一个seq的排序元素  else 一维数组
	 * @param $ttl
	 * @return bool
	 */
	public function insertRecomDestIds($redis_key,$data,$ttl){
		if(!$data) return false;
		foreach($data as $key=>$row){
			if(is_array($row)){
				$this->redis->zadd($redis_key,$row['seq'],$row['dest_id']);
			}else{
				$this->redis->zadd($redis_key,$key,$row);
			}
		}
		if($ttl){
			$this->redis->expire($redis_key,$ttl);
		}
	}

	public function getRecomDestIds($redis_key,$page=null){
		if($page){
			if(is_array($page)){
				$result=$this->redis->zrange($redis_key,($page['page_num']-1)*$page['page_size'],$page['page_num']*$page['page_size']-1);
			}else{
				$result=$this->redis->zrange($redis_key,0,$page-1);
			}
		}else{
			$result=$this->redis->zrange($redis_key,0,-1);
		}
		return $result?$result:false;
	}

	public function getZrevrange($redis_key, $begin = 0, $end = -1, $with_scores = false){
		if($with_scores){
			return $this->redis->Zrevrange($redis_key, $begin, $end, true);
		}else{
			return $this->redis->Zrevrange($redis_key, $begin, $end);
		}
	}

    public function insertTravelList($redis_key,$data,$ttl){
        $value=serialize($data);
        $this->redis->set($redis_key,$value);
        if($ttl){
            $this->redis->setex($redis_key,$ttl,$value);
        }
    }
    public function getTravelList($redis_key){
        $value=$this->redis->get($redis_key);
        if($value){
            return unserialize($value);
        }
    }

	public function setTripList($trip_data,$hmap_key){
		if(!$trip_data) return false;
		foreach($trip_data as $key=>$row) {
            $this->redis->hset($hmap_key . $row['trip_id'], 'trip_id', $row['trip_id']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'title', $row['title']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'thumb', $row['thumb']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'username', $row['username']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'elite', $row['elite']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'init_hits', $row['init_hits']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'init_praise', $row['init_praise']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'day_count', $row['day_count']);
            $this->redis->hset($hmap_key . $row['trip_id'], 'publish_time', $row['publish_time']);
			$this->redis->hset($hmap_key . $row['trip_id'], 'memo', $row['memo']);
		}
	}

	public function getTripData($hmap_key){
		$value=$this->redis->hgetall($hmap_key);
		return $value;
	}
	public function setArrayData($redis_key,$data,$ttl){
		$value=serialize($data);
		$this->redis->set($redis_key,$value);
		if($ttl){
			$this->redis->setex($redis_key,$ttl,$value);
		}
	}
	public function getArrayData($redis_key){
		$value=$this->redis->get($redis_key);
		if($value){
			return unserialize($value);
		}
	}
	public function setListData($redis_key,$data,$ttl){
		if($data){
			$this->redis->rpush($redis_key,$data);
			if($ttl){
				$this->redis->expire($redis_key,$ttl);
			}
		}
	}
	public function getListData($redis_key,$start,$end){
		return $this->redis->lrange($redis_key,$start,$end);
	}

    /**
     * 写入集合set - sadd
     * @param $redis_key
     * @param $data  srt/array
     * @param $ttl
     * @author liuhongfei
     */
    public function dataSAdd($redis_key, $data, $ttl = false){
        if(isset($data)){
            if(is_array($data)){
                foreach($data as $val){
                    $this->redis->sAdd($redis_key,$val);
                    if($ttl){
                        $this->redis->expire($redis_key,$ttl);
                    }
                }
            }else{
                $this->redis->sAdd($redis_key,$data);
                if($ttl){
                    $this->redis->expire($redis_key,$ttl);
                }
            }
        }
    }

    /**
     * 删除集合set中的元素 - srem
     * @param $redis_key
     * @param $data  srt/array
     * @author liuhongfei
     */
    public function dataSRem($redis_key, $data){
        if($data){
            if(is_array($data)){
                foreach($data as $val){
                    $this->redis->sRem($redis_key,$val);
                }
            }else{
                $this->redis->sRem($redis_key,$data);
            }
        }
    }

    public function dataSMembers($redis_key){
        return $this->redis->sMembers($redis_key);
    }

    /**
     * 写入集合zset - zadd
     * @param $redis_key
     * @param $data  srt/array
     * @param $key  string/array
     * @param $ttl
     * @author liuhongfei
     */
    public function dataZAdd($redis_key, $data, $key, $ttl = false){
        if(isset($data)){
            if(is_array($data)){
                foreach($data as $k => $val){
                    $this->redis->zAdd($redis_key, $val, $key[$k]);
                    if($ttl){
                        $this->redis->expire($redis_key, $ttl);
                    }
                }
            }else{
                $this->redis->zAdd($redis_key, $data, $key);
                if($ttl){
                    $this->redis->expire($redis_key, $ttl);
                }
            }
        }
    }

    /**
     * 删除集合zset中的元素 - zrem
     * @param $redis_key
     * @param $data  srt/array
     * @author liuhongfei
     */
    public function dataZRem($redis_key, $data){
        if($data){
            if(is_array($data)){
                foreach($data as $val){
                    $this->redis->zRem($redis_key,$val);
                }
            }else{
                $this->redis->zRem($redis_key,$data);
            }
        }
    }

    /**
     * 截取zset - zrange
     * @param $redis_key
     * @param int $begin
     * @param $end
     * @param bool $with_scores
     * @return mixed
     * @author liuhongfei
     */
    public function getZRange($redis_key, $begin = 0, $end = -1, $with_scores = false){
        if($with_scores){
            return $this->redis->zrange($redis_key, $begin, $end, true);
        }else{
            return $this->redis->zrange($redis_key, $begin, $end);
        }
    }

	/**
	 * 根据分数取有序集合
	 * @param $redis_key
	 * @param $begin
	 * @param $end
	 * @param null $limit
	 * @param bool $with_scores
	 * @return mixed
	 * @author libiying
	 */
	public function getZRangeByScore($redis_key, $begin, $end, $limit = null, $with_scores = false){
		$option = array();

		if(is_array($limit)){
			$option['limit'] = $limit;
		}
		if($with_scores){
			$option['withscores'] = $with_scores;
		}

		if($option){
			return $this->redis->zRangeByScore($redis_key, $begin, $end, $option);
		}else{
			return $this->redis->zRangeByScore($redis_key, $begin, $end);
		}
	}

	/**
	 * 根据分数取有序集合（反序）
	 * @param $redis_key
	 * @param $begin
	 * @param $end
	 * @param null $limit
	 * @param bool $with_scores
	 * @return mixed
	 * @author libiying
	 */
	public function getZRevRangeByScore($redis_key, $begin, $end, $limit = null, $with_scores = false){
		$option = array();

		if(is_array($limit)){
			$option['limit'] = $limit;
		}
		if($with_scores){
			$option['withscores'] = $with_scores;
		}

		if($option){
			return $this->redis->zRevRangeByScore($redis_key, $begin, $end, $option);
		}else{
			return $this->redis->zRevRangeByScore($redis_key, $begin, $end);
		}
	}

    /**
     * 返回有序集 key 的基数
     * @param $redis_key
     * @return mixed
     * @author liuhongfei
     */
    public function getZCard($redis_key){
        return $this->redis->zCard($redis_key);
    }

    /**
     * 返回序列 key 的基数
     * @param $redis_key
     * @return mixed
     * @author liuhongfei
     */
    public function getSCard($redis_key){
        return $this->redis->sCard($redis_key);
    }

    /**
     * 为有序集 key 的成员 member 的 score 值加上增量 increment
     * @param $redis_key
     * @param $increment
     * @param $member
     * @return mixed
     * @author liuhongfei
     */
    public function dataZIncrBy($redis_key, $increment, $member){
        return $this->redis->zIncrBy($redis_key, $increment, $member);
    }

    /**
     * 返回有序集 key 中，成员 member 的 score 值。
     * @param $redis_key
     * @param $member
     * @return mixed
     * @author liuhongfei
     */
    public function dataZScore($redis_key, $member){
        return $this->redis->zScore($redis_key, $member);
    }


    /**
     * 判断 member 元素是否集合 key 的成员。
     * @param $redis_key
     * @param $member
     * @return mixed
     * @author liuhongfei
     */
    public function dataSiSMember($redis_key, $member){
        return $this->redis->sIsMember($redis_key, $member);
    }

    public function dataDelete($redis_key){
        return $this->redis->del($redis_key);
    }


    public function dataSort($redis_key, $condition_array){
        return $this->redis->sort($redis_key, $condition_array);
    }

    /**
     * hash 的元素个数
     * @param $redis_key
     * @return mixed
     * @author liuhongfei
     */
    public function getHlen($redis_key){
        return $this->redis->hLen($redis_key);
    }

    /**
     * 返回hash中所有键
     * @param $redis_key
     * @return mixed
     * @author liuhongfei
     */
    public function getHkeys($redis_key){
        return $this->redis->hKeys($redis_key);
    }

    /**
     * 删除hash中的域
     * @param $redis_key
     * @param $key
     * @return mixed
     * @author liuhongfei
     */
    public function dataHdel($redis_key, $key){
        return $this->redis->hDel($redis_key, $key);
    }

}
