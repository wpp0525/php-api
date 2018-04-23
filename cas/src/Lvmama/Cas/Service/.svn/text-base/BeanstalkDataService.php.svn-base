<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * Beanstalk 服务类
 *
 * @author mac.zhao
 *        
 */
class BeanstalkDataService extends DataServiceBase {
	
	const BEANSTALK_TRIP_STATISTICS = 'lvmama_trip_statistics';
	
	const BEANSTALK_TRIP_COMMENT = 'lvmama_trip_comment';
	
	const BEANSTALK_TRIP_MSG = 'lvmama_trip_msg';
	
	const BEANSTALK_QA_QUESTION = 'lvmama_qa_question';
	
	/**
	 * 游记内容获取目的地
	 * 
	 * @var unknown
	 * 
	 * mac.zhao
	 */
	const BEANSTALK_TRAVEL_CONTENT_4_DEST = 'lvmama_travel_c4d';
	
	/**
	 * 游记内容获取敏感词
	 * 
	 * @var unknown
	 * 
	 * mac.zhao
	 */
	const BEANSTALK_TRAVEL_CONTENT_4_SENSITIVEWORD = 'lvmama_travel_c4sw';

    // 问答社区
    const BEANSTALK_CQA_ANSWER_CKLIST = "lvmama_cqa_check_zerolist";
    const BEANSTALK_CQA_LIST = 'lvmama_cqa_list';

	/**
	 * 游记图片上传队列
	 *
	 * jianghu
	 */
	const BEANSTALK_TRAVEL_IMAGE_UPLOAD_LIST = 'lvmama_travel_image_upload_list';

	/**
	 * 游记快速审核
	 */
	const BEANSTALK_TRAVEL_QUICK_CHECK_LIST = 'lvmama_travel_quick_check_list';

}