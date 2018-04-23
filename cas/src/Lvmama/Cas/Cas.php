<?php

namespace Lvmama\Cas;

use Lvmama\Cas\Component\MasterSlaveDbAdapter;

use Lvmama\Cas\Service\AdminRealDataService;
use Lvmama\Cas\Service\Ads\AdsBannerDataService;
use Lvmama\Cas\Service\Ads\AdsCampaignDataService;
use Lvmama\Cas\Service\Ads\AdsPropertyDataService;
use Lvmama\Cas\Service\Ads\AdsZoneDataService;
use Lvmama\Cas\Service\ApiDataService;

use Lvmama\Cas\Service\DestTripRelDataService;
use Lvmama\Cas\Service\Ora\MarkChannelDataService;
use Lvmama\Cas\Service\Ora\OrderDataService;
use Lvmama\Cas\Service\PageviewsDataService;
use Lvmama\Cas\Service\LikeDataService;
use Lvmama\Cas\Service\CommentDataService;

use Lvmama\Cas\Service\MoRecommendDataService;
use Lvmama\Cas\Service\SemOrderDataService;
use Lvmama\Cas\Service\SubjectDataService;
use Lvmama\Cas\Service\TripDataService;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\TravelCommentTemplateDataService;
use Lvmama\Cas\Service\VestUserDataService;
use Lvmama\Cas\Service\TravelBonusDataService;

use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;

use Lvmama\Cas\Service\CoremetricsDataService;

/*******目的地相关表的数据服务类引用***************/
use Lvmama\Cas\Service\DestinationDataService;
use Lvmama\Cas\Service\DestBaseDataService;
use Lvmama\Cas\Service\DestDetailDataService;
use Lvmama\Cas\Service\DestRelationDataService;
use Lvmama\Cas\Service\DestRecomDataService;
use Lvmama\Cas\Service\DestSumaryDataService;
use Lvmama\Cas\Service\DestTravelDataService;
use Lvmama\Cas\Service\DestApiDataService;
use Lvmama\Cas\Service\ImageDataService;
use Lvmama\Cas\Service\FoodDataService;
use Lvmama\Cas\Service\TraceDataService;
use Lvmama\Cas\Service\TravelDataService;
use Lvmama\Cas\Service\PkCountDataService;
use Lvmama\Cas\Service\SegmentDataService;
use Lvmama\Cas\Service\ScenicViewspotDataService;
use Lvmama\Cas\Service\MoPraiseDataService;
use Lvmama\Cas\Service\MoCommentDataService;
use Lvmama\Cas\Service\MoFavoriteDataService;
use Lvmama\Cas\Service\MoConfigureDataService;
use Lvmama\Cas\Service\SPictureDataService;
use Lvmama\Cas\Service\CombinationDataService;

/********************lmm_seo************/
use Lvmama\Cas\Service\SeoCategoryDataService;
use Lvmama\Cas\Service\SeoCrawlerUrlDataService;
use Lvmama\Cas\Service\SeoKeywordUrlDataService;
use Lvmama\Cas\Service\SeoKeywordUrlRelatedDataService;
use Lvmama\Cas\Service\SeoManualCrawlerDataService;
use Lvmama\Cas\Service\SeoManualUrlDataService;

use Lvmama\Cas\Service\SeoDestCategoryDataService;
use Lvmama\Cas\Service\SeoDestKeywordDataService;
use Lvmama\Cas\Service\SeoDestVariableDataService;
use Lvmama\Cas\Service\SeoDestFilterDataService;
use Lvmama\Cas\Service\SeoTemplateBaseDataService;
use Lvmama\Cas\Service\SeoModuleDataService;
use Lvmama\Cas\Service\SeoTemplateVariableDataService;
use Lvmama\Cas\Service\SeoModuleVariableDataService;
use Lvmama\Cas\Service\SeoTemplateModuleDataService;
use Lvmama\Cas\Service\SeoVariableGroupService;

use Lvmama\Cas\Service\SeoVstRouteDataService;
use Lvmama\Cas\Service\SeoVstTicketDataService;
use Lvmama\Cas\Service\SeoVstHotelDataService;

use Lvmama\Cas\Service\SeoSubjectBlockService;
use Lvmama\Cas\Service\SeoSubjectProductService;


/** 游记数据服务类引用 **/
use Lvmama\Cas\Service\TravelDataServiceBase;
use Lvmama\Cas\Service\TrTravelDataService;
use Lvmama\Cas\Service\TrTravelContentDataService;
/** 游记数据服务类引用 **/

/**
 * 第三方相关数据服务
 */
use Lvmama\Cas\Service\UserDataService;
use Lvmama\Cas\Service\SensitiveWordDataService;
use Lvmama\Cas\Service\ProductInfoDataService;
use Lvmama\Cas\Service\EsDataService;
use Lvmama\Cas\Service\ModuleDataServiceBase;
use Lvmama\Cas\Service\ExternalApiDataService;

/*******问答相关表的数据服务类引用***************/

use Lvmama\Cas\Service\QaAdminAnswerDataService;
use Lvmama\Cas\Service\QaAnswerDataService;
use Lvmama\Cas\Service\QaAnswerTemplateDataService;
use Lvmama\Cas\Service\QaAnswerTemplateTagRelDataService;
use Lvmama\Cas\Service\QaQuestionDataService;
use Lvmama\Cas\Service\QaQuestionDestRelDataService;
use Lvmama\Cas\Service\QaQuestionProductRelDataService;
use Lvmama\Cas\Service\QaQuestionTagRelDataService;
use Lvmama\Cas\Service\QaSlideShowDataService;
use Lvmama\Cas\Service\QaTagCategoryDataService;
use Lvmama\Cas\Service\QaTagDataService;
use Lvmama\Cas\Service\QaTagProductRelDataService;


/***************************   问答  ***************************/
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Cas\Service\QaQuestionStatisticsDataService;

/***************************   新版cms权限  ***************************/
use Lvmama\Cas\Service\StaffBaseDataService;
use Lvmama\Cas\Service\StaffRoleDataService;
use Lvmama\Cas\Service\RoleBaseDataService;
use Lvmama\Cas\Service\RoleFuncDataService;
use Lvmama\Cas\Service\FuncBaseDataService;
use Lvmama\Cas\Service\LogBaseDataService;

/***************************   新版cms内容 ***************************/
use Lvmama\Cas\Service\DistBaseDataService;
use Lvmama\Cas\Service\DistBaseIpService;
use Lvmama\Cas\Service\DestinBaseDataService;
use Lvmama\Cas\Service\DestinBaseMultiRelationDataService;
use Lvmama\Cas\Service\DestinRelDataService;
use Lvmama\Cas\Service\CoordBaseDataService;
use Lvmama\Cas\Service\DistSignDataService;
use Lvmama\Cas\Service\DestinProductRelDataService;
use Lvmama\Cas\Service\DestDistrictNavService;

use Lvmama\Cas\Service\QaAnswerCommentDataService;
use Lvmama\Cas\Service\QaAnswerExtDataService;

/**************************专题分站内容管理*********************************/
use Lvmama\Cas\Service\SubjectBaseService;
use Lvmama\Cas\Service\SubjectSiteDataService;
use Lvmama\Cas\Service\SubSiteRelDataService;
use Lvmama\Cas\Service\SubWebSiteDataService;

use Lvmama\Cas\Service\Message\MessageDataService;
/**************************专题模版管理****************************/
use Lvmama\Cas\Service\SjTempSubjectService;
use Lvmama\Cas\Service\SjTempSubjectWebRelService;
use Lvmama\Cas\Service\SjTempSubjectCouponRelService;
use Lvmama\Cas\Service\SjTempSubjectVariableService;
use Lvmama\Cas\Service\SjTempEnrollService;

/***************************电子围栏*******************************/
use Lvmama\Cas\Service\FencePoiDataService;

/********************lmm_sem************/
use Lvmama\Cas\Service\SemAccountBaseDataService;
use Lvmama\Cas\Service\SemCampaignBaseDataService;
use Lvmama\Cas\Service\SemAdgroupBaseDataService;
use Lvmama\Cas\Service\SemKeywordBaseDataService;
use Lvmama\Cas\Service\SemCreativeBaseDataService;
use Lvmama\Cas\Service\SemMonitorBaseDataService;
use Lvmama\Cas\Service\SemReportEsDataService;
use Lvmama\Cas\Service\ScanbuyReportEsDataService;

/***************************   产品池   *******************************/
use Lvmama\Cas\Service\ProductPoolDataService;
use Lvmama\Cas\Service\ProductPoolPlusDataService;
use Lvmama\Cas\Service\ProductPoolVstProductDataService;
use Lvmama\Cas\Service\ProductPoolVstDistrictDataService;
use Lvmama\Cas\Service\ProductPoolVstDestDataService;
use Lvmama\Cas\Service\ProductPoolVstGoodsDataService;
use Lvmama\Cas\Service\ProductPoolDistrictProductDataService;
use Lvmama\Cas\Service\ProductPoolDestRelService;
use Lvmama\Cas\Service\ProductPoolProductService;
use Lvmama\Cas\Service\ProductPoolGoodsService;
use Lvmama\Cas\Service\ProductPoolStartdistrictAddtionalService;
use Lvmama\Cas\Service\ProductPoolRedisDataService;
use Lvmama\Cas\Service\PptempZt2BlockDataService;
use Lvmama\Cas\Service\PpTempZt2DataDataService;
use Lvmama\Cas\Service\PptempZt1BlockDataService;
use Lvmama\Cas\Service\PpTempZt1DataDataService;

/***************************优惠券*******************************/
use Lvmama\Cas\Service\TemplateSubjectCouponService;
use Lvmama\Cas\Service\TemplateSubjectCouponRecordsService;

/*************************    lmm_source *******************************/
use Lvmama\Cas\Service\SourceProductRelDataService;

/***************************   酒店周边目的地   *******************************/
use Lvmama\Cas\Service\HtlDestAroundService;
use Lvmama\Cas\Service\HtlProductDestService;

/*************************    lmm_pp *******************************/
use Lvmama\Cas\Service\PpProductDestRelService;

/*****************************  sct 后台 基础  ****************************/
use Lvmama\Cas\Service\SctSystemCoreDataService;

/******************************  lmm_baike  *****************************/
use Lvmama\Cas\Service\BaiKeDataService;

/*************************** 微攻略 *******************************/
use Lvmama\Cas\Service\NewGuideDataServiceBase;
/**
 * Core Application Server
 *
 * @author mac.zhao
 *
 */
class Cas {

	private $di;

	protected $context;

	private $redis;

	private $beanstalk;

    private $productpollv2Redis;

//	private $dbs = array();

	private $services = array();

	private $dbsDynamic = array();
	private $serviceConfigs = array();
	private $dbAdapters = array();

	function __construct($di, $dbsDynamic, $redis, $beanstalk, $singletonRedis = '' ) {
		$this->dbsDynamic = $dbsDynamic;
        $this->di = $di;
//        $this->dbs=$dbs;

		if(array_key_exists('dbcore', $dbsDynamic)) {
			$this->serviceConfigs['api-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\ApiDataService', 'dbKey' => 'dbcore',);
			$this->serviceConfigs['admin-real-service'] = array('serviceName' => 'Lvmama\Cas\Service\AdminRealDataService', 'dbKey' => 'dbcore',);
		}

		if(array_key_exists('dbmodule', $dbsDynamic)) {
			$this->serviceConfigs['pageviews-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\PageviewsDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['like-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\LikeDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['comment-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\CommentDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['mo-subject'] = array('serviceName' => 'Lvmama\Cas\Service\SubjectDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['mo-recommend-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MoRecommendDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['module-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\ModuleDataServiceBase', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['comment-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MoCommentDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['praise-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MoPraiseDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['favorite-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MoFavoriteDataService', 'dbKey' => 'dbmodule',);
			$this->serviceConfigs['configure-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MoConfigureDataService', 'dbKey' => 'dbmodule',);
		}
		/* 微攻略相关数据服务 */
		if(array_key_exists('dbnewguide', $dbsDynamic)) {
			$this->serviceConfigs['new_guide_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\NewGuideDataServiceBase', 'dbKey' => 'dbnewguide',);
		}

		if(array_key_exists('dblvyou', $dbsDynamic)) {
			$this->serviceConfigs['trip-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TripDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['trip-statistics-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TripStatisticsDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['travel-comment-template-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TravelCommentTemplateDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['vest-user-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\VestUserDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['travel-bonus-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TravelBonusDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['recom_dest_service'] =  array('serviceName' => 'Lvmama\Cas\Service\DestRecomDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['dest_sumary_service'] =  array('serviceName' => 'Lvmama\Cas\Service\DestSumaryDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['dest_travel_service'] =  array('serviceName' => 'Lvmama\Cas\Service\DestTravelDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['dest_image_service'] =  array('serviceName' => 'Lvmama\Cas\Service\ImageDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['travel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TravelDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['trace-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TraceDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['segment-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\SegmentDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['destination-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['food-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\FoodDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['scenicviewspot-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\ScenicViewspotDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['spicture-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\SPictureDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['old_dist_sign_service'] = array('serviceName' => 'Lvmama\Cas\Service\DistSignDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['combination-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\CombinationDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['pkcount-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\PkCountDataService', 'dbKey' => 'dblvyou',);
		}
		if(array_key_exists('dbbaike', $dbsDynamic)) {
			$this->serviceConfigs['baike-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\BaiKeDataService', 'dbKey' => 'dbbaike',);
		}

		if(array_key_exists('dbmsg', $dbsDynamic)) {
			$this->serviceConfigs['msg-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MsgDataService', 'dbKey' => 'dblvyou',);
			$this->serviceConfigs['coremetrics-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\MsgDataService', 'dbKey' => 'dblvyou',);
		}

		if(array_key_exists('dbnewlvyou', $dbsDynamic)) {
			$this->serviceConfigs['dest_old_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationDataService', 'dbKey' => 'dbnewlvyou',);
			$this->serviceConfigs['dest_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestBaseDataService', 'dbKey' => 'dbnewlvyou',);
			$this->serviceConfigs['dest_detail_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestDetailDataService', 'dbKey' => 'dbnewlvyou',);
			$this->serviceConfigs['dest_relation_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestRelationDataService', 'dbKey' => 'dbnewlvyou',);
			$this->serviceConfigs['dest_trips_rel_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestTripRelDataService', 'dbKey' => 'dbnewlvyou',);
			/************************电子围栏*************************/
			$this->serviceConfigs['fence_poi_data'] = array('serviceName' => 'Lvmama\Cas\Service\FencePoiDataService', 'dbKey' => 'dbnewlvyou',);
		}

		/* 第三方相关数据服务 */
		$this->services['user-data-service'] = new UserDataService($di, $redis, $beanstalk);
		$this->services['sensitive-word-data-service'] = new SensitiveWordDataService($di, $redis, $beanstalk);
		$this->services['product-info-data-service'] = new ProductInfoDataService($di,$redis,$beanstalk);
		$this->services['es-data-service'] = new EsDataService($di,$redis,$beanstalk);

		$this->services['external-api-data-server'] = new ExternalApiDataService($di,$redis,$beanstalk);
		$this->services['redis_data_service'] =  new RedisDataService($di,'',$redis,$beanstalk);
		$this->services['dest_api_service'] =  new  DestApiDataService($di,'',$redis,$beanstalk);


		/* 游记相关数据服务 */
		if(array_key_exists('dbtravels', $dbsDynamic)) {
			$this->serviceConfigs['travel_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\TravelDataServiceBase', 'dbKey' => 'dbtravels',);
			$this->serviceConfigs['tr-travel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TrTravelDataService', 'dbKey' => 'dbtravels',);
			$this->serviceConfigs['tr-travel-content-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\TrTravelContentDataService', 'dbKey' => 'dbtravels',);
		}

		/* qa */
        if(array_key_exists('dbqa', $dbsDynamic)) {
			$this->serviceConfigs['qaadminanswer-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAdminAnswerDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaanswer-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAnswerDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaanswertemplate-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAnswerTemplateDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaanswertemplatetagrel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAnswerTemplateTagRelDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaquestion-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaQuestionDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaquestiondestrel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaQuestionDestRelDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaquestionproductrel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaQuestionProductRelDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaquestiontagrel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaQuestionTagRelDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qaslideshow-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaSlideShowDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qatagcategory-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaTagCategoryDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qatag-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaTagDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qatagproductrel-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\QaTagProductRelDataService', 'dbKey' => 'dbqa',);

	        /***************************   问答  ***************************/
			$this->serviceConfigs['qa_common_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\QaCommonDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qa_question_statistics_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\QaQuestionStatisticsDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qa_answer_comment_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAnswerCommentDataService', 'dbKey' => 'dbqa',);
			$this->serviceConfigs['qa_answer_ext_data_service'] = array('serviceName' => 'Lvmama\Cas\Service\QaAnswerExtDataService', 'dbKey' => 'dbqa',);
	        /***************************   问答  ***************************/
		}


		/*新版cms权限相关数据服务*/
        if(array_key_exists('dbnewcms', $dbsDynamic)) {
			$this->serviceConfigs['role_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\RoleBaseDataService', 'dbKey' => 'dbnewcms',);
			$this->serviceConfigs['func_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\FuncBaseDataService', 'dbKey' => 'dbnewcms',);
			$this->serviceConfigs['log_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\LogBaseDataService', 'dbKey' => 'dbnewcms',);
        }

        /****************  sct 新权限 *************************************/
        if(array_key_exists('sctsystem',$dbsDynamic)){
            $this->serviceConfigs['sct_system_core'] = array('serviceName' => 'Lvmama\Cas\Service\SctSystemCoreDataService', 'dbKey' => 'sctsystem',);
            $this->serviceConfigs['role_func_service'] = array('serviceName' => 'Lvmama\Cas\Service\RoleFuncDataService', 'dbKey' => 'sctsystem',);
            $this->serviceConfigs['staff_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\StaffBaseDataService', 'dbKey' => 'sctsystem',);
            $this->serviceConfigs['staff_role_service'] = array('serviceName' => 'Lvmama\Cas\Service\StaffRoleDataService', 'dbKey' => 'sctsystem',);
        }

		if(array_key_exists('sctlogger',$dbsDynamic)){
			$this->serviceConfigs['sct_logger_service'] = array('serviceName' => 'Lvmama\Cas\Service\SctLoggerDataService', 'dbKey' => 'sctlogger',);
		}

        /*** lmm_seo***/
		if(array_key_exists('dbseo', $dbsDynamic)) {
			$this->serviceConfigs['seo_vartypenavigation_service'] = array('serviceName' => 'Lvmama\Cas\Service\VartypeNavigationService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_category_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoCategoryDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_crawler_url_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoCrawlerUrlDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_keyword_url_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoKeywordUrlDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_keyword_url_related_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoKeywordUrlRelatedDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_manual_crawler_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoManualCrawlerDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_manual_url_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoManualUrlDataService', 'dbKey' => 'dbseo',);

			$this->serviceConfigs['seo_dest_category_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoDestCategoryDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_dest_keyword_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoDestKeywordDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_dest_variable_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoDestVariableDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_dest_filter_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoDestFilterDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_template_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoTemplateBaseDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_module_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoModuleDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_template_variable_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoTemplateVariableDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_module_variable_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoModuleVariableDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_template_module_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoTemplateModuleDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_variable_group_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoVariableGroupService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_vst_route_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoVstRouteDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_vst_ticket_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoVstTicketDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_vst_hotel_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoVstHotelDataService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_subject_block_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoSubjectBlockService', 'dbKey' => 'dbseo',);
			$this->serviceConfigs['seo_subject_product_service'] = array('serviceName' => 'Lvmama\Cas\Service\SeoSubjectProductService', 'dbKey' => 'dbseo',);
		}

		/*新版cms内容相关数据服务*/
        if(array_key_exists('dbvst', $dbsDynamic)) {
			$this->serviceConfigs['dist_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\DistBaseDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['dist_base_ip_service'] = array('serviceName' => 'Lvmama\Cas\Service\DistBaseIpService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['destin_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinBaseDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['destin_multi_relation_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinBaseMultiRelationDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['destin_rel_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinRelDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['coord_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\CoordBaseDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['dist_sign_service'] = array('serviceName' => 'Lvmama\Cas\Service\DistSignDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['dest_product_rel_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinProductRelDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['dest_district_nav_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestDistrictNavService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['destin_multi_relation_base_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinBaseMultiRelationDataService', 'dbKey' => 'dbvst',);
			$this->serviceConfigs['dest_product_rel_v2_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestProductRelV2Service', 'dbKey' => 'dbvst',);

            $this->serviceConfigs['destination_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['destination_time_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationTimeService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['destination_address_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationAddressService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['destination_contact_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationContactService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['mo_recommend_service'] = array('serviceName' => 'Lvmama\Cas\Service\MoRecommendService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['mo_recommend_block_service'] = array('serviceName' => 'Lvmama\Cas\Service\MoRecommendBlockService', 'dbKey' => 'dbvst',);
            /**** 新cms后台美食管理和购物管理****/
            $this->serviceConfigs['destination_food_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationFoodService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['food_dest_service'] = array('serviceName' => 'Lvmama\Cas\Service\FoodDestService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['goods_dest_service'] = array('serviceName' => 'Lvmama\Cas\Service\GoodsDestService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['destination_goods_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationGoodsService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['elite_image_service'] = array('serviceName' => 'Lvmama\Cas\Service\EliteImageService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['destination_transportation_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationTransportationService', 'dbKey' => 'dbvst',);
            /*******美食管理结束*************/
            $this->serviceConfigs['destination_trips_rel_service'] = array('serviceName' => 'Lvmama\Cas\Service\DestinationTripRelDataService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['monthrec_service'] = array('serviceName' => 'Lvmama\Cas\Service\MonthrecDataService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['mo_subject_service'] = array('serviceName' => 'Lvmama\Cas\Service\MoSubjectService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['mo_subject_relation_service'] = array('serviceName' => 'Lvmama\Cas\Service\MoSubjectRelationService', 'dbKey' => 'dbvst',);
            $this->serviceConfigs['mo_attachments_service'] = array('serviceName' => 'Lvmama\Cas\Service\MoAttachmentsService', 'dbKey' => 'dbvst',);

            $this->serviceConfigs['vst_dest_sumary_service'] =  array('serviceName' => 'Lvmama\Cas\Service\VstDestSumaryDataService', 'dbKey' => 'dbvst',);

        }

		/*广告系统相关服务*/
		if(array_key_exists('dbads', $dbsDynamic)){
			$this->serviceConfigs['ads_banner_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ads\AdsBannerDataService', 'dbKey' => 'dbads',);
			$this->serviceConfigs['ads_zone_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ads\AdsZoneDataService', 'dbKey' => 'dbads',);
			$this->serviceConfigs['ads_campaign_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ads\AdsCampaignDataService', 'dbKey' => 'dbads',);
			$this->serviceConfigs['ads_property_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ads\AdsPropertyDataService', 'dbKey' => 'dbads',);
		}

		/****************lmm_subject*************************************/
		if(array_key_exists('dbsub', $dbsDynamic)){
			$this->serviceConfigs['sub_list'] = array('serviceName' => 'Lvmama\Cas\Service\SubjectBaseService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sub_site'] = array('serviceName' => 'Lvmama\Cas\Service\SubjectSiteDataService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sub_web_site'] = array('serviceName' => 'Lvmama\Cas\Service\SubWebSiteDataService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sub_site_rel'] = array('serviceName' => 'Lvmama\Cas\Service\SubSiteRelDataService', 'dbKey' => 'dbsub',);

			$this->serviceConfigs['temp_subject'] = array('serviceName' => 'Lvmama\Cas\Service\SjTempSubjectService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['temp_subject_web_rel'] = array('serviceName' => 'Lvmama\Cas\Service\SjTempSubjectWebRelService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['temp_subject_coupon_rel'] = array('serviceName' => 'Lvmama\Cas\Service\SjTempSubjectCouponRelService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['temp_subject_variable'] = array('serviceName' => 'Lvmama\Cas\Service\SjTempSubjectVariableService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sj_template_subject_coupon_service'] = array('serviceName' => 'Lvmama\Cas\Service\TemplateSubjectCouponService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sj_template_subject_coupon_records_service'] = array('serviceName' => 'Lvmama\Cas\Service\TemplateSubjectCouponRecordsService', 'dbKey' => 'dbsub',);
			$this->serviceConfigs['sj_template_enroll'] = array('serviceName' => 'Lvmama\Cas\Service\SjTempEnrollService', 'dbKey' => 'dbsub',);

		}
                /****************lmm_channel*************************************/
        if (array_key_exists('dbchannel', $dbsDynamic)) {
            $this->serviceConfigs['temp_channel'] = array('serviceName' => 'Lvmama\Cas\Service\ChTempChannelService', 'dbKey' => 'dbchannel',);
            $this->serviceConfigs['temp_channel_variable'] = array('serviceName' => 'Lvmama\Cas\Service\ChTempChannelVariableService', 'dbKey' => 'dbchannel',);
            $this->serviceConfigs['ch_website_spm_rel'] = array('serviceName' => 'Lvmama\Cas\Service\ChWebsiteSpmRelService', 'dbKey' => 'dbchannel',);
        }

		/*** lmm_sem***/
		if(array_key_exists('dbsem', $dbsDynamic)) {
			$this->serviceConfigs['sem_account_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemAccountBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_campaign_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemCampaignBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_adgroup_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemAdgroupBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_keyword_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemKeywordBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_reoprt_all_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemAccountReportAllService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_creative_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemCreativeBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_monitor_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemMonitorBaseDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_report_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemReportEsDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_order_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemOrderDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_user_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemUserDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['scan_report_service'] = array('serviceName' => 'Lvmama\Cas\Service\ScanbuyReportEsDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_budget_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemBudgetDataService', 'dbKey' => 'dbsem',);
			$this->serviceConfigs['sem_promotion_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemPromotionDataService', 'dbKey' => 'dbsem',);
		}
		if(array_key_exists('dbcoupon', $dbsDynamic)) {
			$this->serviceConfigs['sem_coupon_service'] = array('serviceName' => 'Lvmama\Cas\Service\SemCouponDataService', 'dbKey' => 'dbcoupon',);
		}

		if(array_key_exists('lvmama_ver', $dbsDynamic)){
			$this->serviceConfigs['ora_order_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ora\OrderDataService', 'dbKey' => 'lvmama_ver',);
		}
		if(array_key_exists('lvmama_pet', $dbsDynamic)){
			$this->serviceConfigs['ora_mark_channel_service'] = array('serviceName' => 'Lvmama\Cas\Service\Ora\MarkChannelDataService', 'dbKey' => 'lvmama_pet',);
		}

        if(array_key_exists('dbhtldest', $dbsDynamic)) {
        	$this->serviceConfigs['prod_product_attr'] = array('serviceName' => 'Lvmama\Cas\Service\HtlDestAroundService', 'dbKey' => 'dbhtldest',);
        	$this->serviceConfigs['hd_product_dest'] = array('serviceName' => 'Lvmama\Cas\Service\HtlProductDestService', 'dbKey' => 'dbhtldest',);
        }

        /************************     产品池开始    *************************/
		if(array_key_exists('dbpropool', $dbsDynamic)) {
			$this->serviceConfigs['product_pool_data'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_plus_data'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolPlusDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_vst_product'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolVstProductDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_vst_dest'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolVstDestDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_vst_district'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolVstDistrictDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_vst_goods'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolVstGoodsDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_district_product'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolDistrictProductDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['pp_product_dest_rel'] = array('serviceName' => 'Lvmama\Cas\Service\PpProductDestRelService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_dest_rel'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolDestRelService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_product'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolProductService', 'dbKey' => 'dbpropool',);
            $this->serviceConfigs['product_pool_goods'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolGoodsService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_startdistrict_addtional'] = array('serviceName' => 'Lvmama\Cas\Service\ProductPoolStartdistrictAddtionalService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['product_pool_redis_data'] = array('serviceName'=>'Lvmama\Cas\Service\ProductPoolRedisDataService','dbKey'=>'dbpropool',);
			$this->serviceConfigs['pp_temp_zt2_block'] = array('serviceName' => 'Lvmama\Cas\Service\PptempZt2BlockDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['pp_temp_zt2_data'] = array('serviceName' => 'Lvmama\Cas\Service\PpTempZt2DataDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['pp_temp_zt1_block'] = array('serviceName' => 'Lvmama\Cas\Service\PptempZt1BlockDataService', 'dbKey' => 'dbpropool',);
			$this->serviceConfigs['pp_temp_zt1_data'] = array('serviceName' => 'Lvmama\Cas\Service\PpTempZt1DataDataService', 'dbKey' => 'dbpropool',);
		}
        /************************     产品池结束    *************************/

		/********************* lmm_sys 服务器资源管理 start *****************/
		if(array_key_exists('dblmmsys', $dbsDynamic)) {
			$this->serviceConfigs['sys_data_service'] = array('serviceName' => '\Lvmama\Cas\Service\SysDataService', 'dbKey' => 'dblmmsys',);
		}
		/********************* lmm_sys 服务器资源管理 end *****************/

		/************************     lmm_source 开始    *************************/
		if(array_key_exists('dbsource', $dbsDynamic)) {
			$this->serviceConfigs['source_product_dest_service'] = array('serviceName' => 'Lvmama\Cas\Service\SourceProductRelDataService', 'dbKey' => 'dbsource',);
		}
        /************************     lmm_source 结束    *************************/

        /************************新站内信*************************/
        if(array_key_exists('dbnewmsg', $dbsDynamic)) {
			$this->serviceConfigs['message-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\Message\MessageDataService', 'dbKey' => 'dbnewmsg',);
        }

		/************************ BBS [用于BBS迁移数据用] *************************/
		if(array_key_exists('dbbbs', $dbsDynamic)) {
			$this->serviceConfigs['bbs-data-service'] = array('serviceName' => 'Lvmama\Cas\Service\BbsDataService', 'dbKey' => 'dbbbs',);
		}
        /************* BBS 结束 ************/


		$this->redis = $redis;
		$this->beanstalk = $beanstalk;
		$this->singletonRedis= $singletonRedis;
	}



	public function getRedis($node = ''){
		if ($node) {
			$this->singletonRedis->setClient($node);
			return $this->singletonRedis;
		}
		else {
			return $this->redis;
		}
	}

	public function getBeanstalk(){
		return $this->beanstalk;
	}

    public function getDbServer($dbKey){
//        if(array_key_exists($db_key,$this->dbs)){
//            return $this->dbs[$db_key];
//        }
		$db = null;
		if(isset($this->dbAdapters[$dbKey])){
			$db = $this->dbAdapters[$dbKey];
		}else{
			$dbAdapter = $this->dbsDynamic[$dbKey]['dbAdater'];
			$db = new $dbAdapter($this->dbsDynamic[$dbKey]['config']);
			if(isset($this->dbsDynamic[$dbKey]['setter']) && $setter = $this->dbsDynamic[$dbKey]['setter']){
				foreach ($setter as $key => $value){
					$db->set($key, $value);
				}
			}
			$this->dbAdapters[$dbKey] = $db;
		}
		return $db;
    }
	/**
	 * 获取服务类对象
	 *
	 * @param string $service_id
	 * @throws \Exception
	 * @return multitype:
	 */
	public function get($service_id) {
		if (isset($this->services[$service_id])){
			return $this->services[$service_id];
		}else if(isset($this->serviceConfigs[$service_id])){
			$serviceName = $this->serviceConfigs[$service_id]['serviceName'];
			$dbKey = $this->serviceConfigs[$service_id]['dbKey'];
			$db = self::getDbServer($dbKey);
			$service = new $serviceName($this->di, $db, $this->redis, $this->beanstalk);
			$this->services[$service_id] = $service;
			return $service;
		}else{
			throw new \Exception('服务不存在或未定义: ' . $service_id);
		}
	}
}
