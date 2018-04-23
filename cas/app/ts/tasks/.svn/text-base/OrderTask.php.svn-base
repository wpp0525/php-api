<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use \Baidusearch\Account\AccountService;
use \Baidusearch\Account\GetAccountInfoRequest;
use \Baidusearch\Report\GetRealTimeDataRequest;
use \Baidusearch\Report\ReportRequestType;
use \Baidusearch\Report\ReportService;
use \Baidusearch\Keyword\GetWordRequest;
use \Baidusearch\Keyword\KeywordService;
use \Baidusearch\Adgroup\AdgroupService;
use \Baidusearch\Adgroup\GetAdgroupRequest;
use \Baidusearch\Campaign\CampaignService;
use \Baidusearch\Campaign\GetCampaignRequest;
use \Lvmama\Cas\Component\Kafka\Producer;

/**
 * oracle 订单数据 定时任务
 *
 * @author libiying
 *
 */
class OrderTask extends Task {

    /**
     *
     * @var \Phalcon\DiInterface
     */
    private $di;


    /**
     * @var \Lvmama\Cas\Service\Ora\OrderDataService;
     */
    private $ora_order;

    /**
     * @var \Lvmama\Cas\Service\Ora\MarkChannelDataService
     */
    private $ora_mark_channel;

    /**
     * @var \Lvmama\Cas\Service\SemOrderDataService;
     */
    private $sem_order;

    /**
     * @var \Lvmama\Cas\Service\SemKeywordBaseDataService;
     */
    private $sem_keyword;

    /**
     * @var \Lvmama\Cas\Service\RedisDataService;
     */
//    private $redis;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );

        $this->di = $dependencyInjector;
//        $this->ora_order = $this->di->get('cas')->get('ora_order_service');
//        $this->ora_mark_channel = $this->di->get('cas')->get('ora_mark_channel_service');
//        $this->sem_order = $this->di->get('cas')->get('sem_order_service');
//        $this->sem_keyword = $this->di->get('cas')->get('sem_keyword_service');
//        $this->redis = $dependencyInjector->get('cas')->get('redis_data_service');
    }


    /**
     * 拉取oracle losc 聚合订单数据，并存入Kafka
     */
    public function putGroupOrder2KafkaAction($params){
        $this->ora_mark_channel = $this->di->get('cas')->get('ora_mark_channel_service');
        $this->sem_order = $this->di->get('cas')->get('sem_order_service');
        $this->sem_keyword = $this->di->get('cas')->get('sem_keyword_service');
        ini_set('memory_limit','512M');

        $type = isset($params[0]) ? $params[0] : 'hour';
        if(!in_array($type, array('hour', 'day'))){
            return;
        }
        $before_num = isset($params[1]) ? $params[1] : ($type == 'hour' ? '-2' : '-1');
        //平台详见Semsearch/SearchType
        $platform = isset($params[2]) ? $params[2] : 1;

        $config = $this->di->get('config')->kafka->baiduSearchProducer->toArray();
        $producer = new Producer($config);
        if($type == 'day'){
            if($platform == \Semsearch\SearchType::BAIDU){
                $fp = fopen(APPLICATION_PATH . '/logs/reportdata/order.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt', 'a');
            }else if($platform == \Semsearch\SearchType::SANLIULING){
                $fp = fopen(APPLICATION_PATH . '/logs/reportdata/order.socom.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt', 'a');
            }else if($platform == \Semsearch\SearchType::SHENMA){
                $fp = fopen(APPLICATION_PATH . '/logs/reportdata/order.smcn.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt', 'a');
            }else if($platform == \Semsearch\SearchType::SOGOU){
                $fp = fopen(APPLICATION_PATH . '/logs/reportdata/order.sogou.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt', 'a');
            }
        }

        $loscs = array();
        if($type == 'hour'){
            $time = date('Y-m-d H:i:s', strtotime($before_num . ' hour'));
            $h_time = date('Y-m-d H:00', strtotime($before_num . ' hour'));

            $condition = array(
                "PAYMENT_TIME >" => "'$time'",
                "PAYMENT_STATUS =" => "'PAYED'",
            );
            $select = " distinct LOSC_ID, sem_order.ORDER_ID, DISTRIBUTOR_CODE, DISTRIBUTOR_ID, ACTUAL_AMOUNT, DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d %H:00') as PAYMENT_TIME";
            $loscs = $this->sem_order->getOrderWithLosc($condition, $select, null);
        }else if($type == 'day'){
            $time = date('Y-m-d', strtotime($before_num . ' day'));
            $after_time = date('Y-m-d', strtotime(($before_num + 1) . ' day'));
            $condition = array(
                "PAYMENT_TIME >= " => "'$time'",
                "PAYMENT_TIME < " => "'$after_time'",
                "PAYMENT_STATUS =" => "'PAYED'",
            );
            $select = " distinct LOSC_ID, sem_order.ORDER_ID, DISTRIBUTOR_CODE, DISTRIBUTOR_ID, ACTUAL_AMOUNT, DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d') as PAYMENT_TIME";
            $loscs = $this->sem_order->getOrderWithLosc($condition, $select, null);
        }
        if(!$loscs){
            echo date('Y-m-d H:i:s', time()) . ":" . "loscs为空,params:" . json_encode($params) . "\n";
        }

        //查询外站losc
        $losc_ids = array();
        foreach ($loscs as $losc){
            if(!in_array($losc['LOSC_ID'], $losc_ids)){
                $losc_ids[] = $losc['LOSC_ID'];
            }
        }
        $marks = array();
        $step = 1000;
        for($i = 0; $i < count($losc_ids); $i = $i + $step){
            $ids = array_slice($losc_ids, $i, $step);
            $condition = array(
                'CHANNEL_CODE in' =>"('" . implode("','", $ids) . "')",
                'RANGE = ' => "'OUTTER'",
                'APPLICATION_TYPE = ' => "'losc'",
            );
            $marks = array_merge($marks, $this->ora_mark_channel->getMarkChannelList($condition));
        }

        //剔除不符合要求的losc
        $h_loscs = array();
        foreach ($marks as $mark){
            foreach ($loscs as $losc){
                if($mark['CHANNEL_CODE'] == $losc['LOSC_ID']){
                    $h_loscs[] = $losc;
                }
            }
        }
        $loscs = $h_loscs;
        unset($h_loscs);

        //计算金额（应付金额，奖金金额？）
        $groups = array();
        $amounts = array();
        foreach ($loscs as $losc){
            //keyword - losc 对应关系存在的
//            $losc_key = str_replace('{losc}', $losc['LOSC_ID'], \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_LOSC_KEYWORD);
//            $losc_exist = $this->redis->dataGet($losc_key);
            $losc_exist = $this->sem_keyword->getOneKeyword(array('losc = ' => "'" . $losc['LOSC_ID'] . "'", 'platform = ' => $platform), 'keywordId');
            if($losc_exist){
                $amounts[$losc['ORDER_ID']][] = $losc;
            }
        }
        unset($loscs);

        foreach ($amounts as $amount){
            $count = count($amount);
            //losc分成
            $percentage = array(1);
            if($count == 4){
                $percentage = array(0.3, 0.2, 0.2, 0.3);
            }else if($count == 3){
                $percentage = array(0.4, 0.2, 0.4);
            }else if($count == 2){
                $percentage = array(0.5, 0.5);
            }
            foreach ($amount as $k => $a){
                //过滤可能导致金额不准确的数据
                if(isset($h_time) && $h_time == $a['PAYMENT_TIME']){
                    continue;
                }

                //入库好测试
                $a['TYPE'] = $type;
                $this->sem_order->insertOrderLoscReport($a);

                //区分pc和移动
                $pcOrMobile = $this->pcOrMobile($a);
                if($pcOrMobile == 3){
                    continue;
                }
                $keys = array();
                $keys[$pcOrMobile] = $pcOrMobile . ':' . $a['LOSC_ID'] . ':' . $a['PAYMENT_TIME'];
                //不区分pc和移动
                if($pcOrMobile == '1' || $pcOrMobile == '2'){
                    $keys['0'] = '0:' .  $a['LOSC_ID'] . ':' . $a['PAYMENT_TIME'];
                }
                foreach ($keys as $p_m => $key){
                    if(!isset($groups[$key])){
                        $groups[$key] = array(
                            'losc' => $a['LOSC_ID'],
                            'amount' => 0,
                            'orderNum' => 0,
                            'date' => $a['PAYMENT_TIME'],
                            'device' => $p_m,
                            'unitOfTime' => $type == 'hour' ? 7 : 5,
                        );
                    }
                    $groups[$key]['amount'] += $a['ACTUAL_AMOUNT'] * $percentage[$k] * 0.01;
                    $groups[$key]['orderNum'] ++;
                }
            }
        }
        unset($amounts);

        //查询losc对应的关键词，推入kafka
        foreach ($groups as $value){
            //存入redis -- 暂弃
//            $redis_key = str_replace('{device}:{loscId}:{Y-i-d H}', $key, \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_LOSC_ORDER);
//            $ttl = 60*60*24*2;
//            $this->redis->dataSet($redis_key, json_encode($value), $ttl);
//            echo date('Y-m-d H:i:s', time()) . " redis_key:" . $redis_key . ' data:' . json_encode($value) . "\n";

            $keywords = $this->sem_keyword->getFullKeywordList(
                array('losc = ' =>"'" . $value['losc'] . "'",'sem_keyword.platform = ' => $platform),
                'DISTINCT userName, adgroupName, campaignName, keywordId as id, keyword',
                array('adgroup' ,'campaign' ,'account'),
                6000
            );
            if(!$keywords){
                continue;
            }

            $count = count($keywords);
            $size = 20;
            $data = array();
            foreach ($keywords as $key => $keyword){
                $data[] = array_merge($value, $keyword);
                if(count($data) == $size || $key + 1 == $count){
                    if($type == 'hour'){
                        //分时报表推送kafka进行实时处理
                        $producer->sendMsg(json_encode($data));
                        echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
                    }else if($type == 'day'){
                        //分日报表写入文本直接导入到库
                        $content = '';
                        foreach ($data as $d){
                            $content .= implode("\t", $d) . "\n";
                        }
                        fwrite($fp, $content);
                    }
                    $data = array();
                }
            }
            usleep(50);
        }
        if($type == 'day') {
            fclose($fp);
        }
    }

    public function scanBuyOrderAction($params){
        $this->sem_order = $this->di->get('cas')->get('sem_order_service');
        ini_set('memory_limit','256M');

        $type = isset($params[0]) ? $params[0] : 'hour';
        if(!in_array($type, array('hour', 'day'))){
            return;
        }
        $before_num = isset($params[1]) ? $params[1] : '-1';

        $config = $this->di->get('config')->kafka->buyOrderProducer->toArray();
        $producer = new Producer($config);

        $time = date('Y-m-d H:i:s', strtotime($before_num . ' hour'));
        $condition = array(
            'sem_order.DISTRIBUTOR_ID = ' => 6,
            "sem_order.PAYMENT_STATUS =" => "'PAYED'",
//            'sem_order.order_id = ' => 20005168,
//            'sem_order.user_id = ' => "'40288ae13adfba21013adfc3f2050005'",
            'sem_order.payment_time >=' => "'$time'",
        );
        $select  = " sem_order.order_id, sem_order.actual_amount, sem_order.payment_time, ";
        $select  .= " sem_user.user_id, sem_user.user_name, sem_user.created_date as register_date, ";
        $select  .= " sem_order_item.order_item_id, sem_order_item.quantity, sem_order_item.supp_goods_id, sem_order_item.supp_goods_name, sem_order_item.product_id, sem_order_item.product_name ";

        $orders = $this->sem_order->getFullOrderList($condition, $select);
        $count = count($orders);
        $num = 0;
        $size = 20;
        $data = array();
        foreach ($orders as $order){
            $num ++;

            $data[] = $order;
            if(count($data) == $size || $num == $count){
                $producer->sendMsg(json_encode($data));
                echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
                $data = array();
                usleep(50);
            }
        }

    }


    /**
     * 拉取oracle 全部losc订单数据
     */
    public function loscOrderAction($params = array()){
        $this->ora_mark_channel = $this->di->get('cas')->get('ora_mark_channel_service');
        $this->sem_order  = $this->di->get('cas')->get('sem_order_service');
        ini_set('memory_limit','256M');

        $type = isset($params[0]) ? $params[0] : 'day';
        if(!in_array($type, array('hour', 'day'))){
            return;
        }
        $before_num = isset($params[1]) ? $params[1] : '-1';

        if ($type == 'day') {
        	$fname = APPLICATION_PATH . '/logs/reportdata/losc.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt';
            $sjfname = APPLICATION_PATH . '/logs/reportdata/subject_losc.' . date('Y-m-d', strtotime($before_num . ' day')) . '.txt';
            file_exists($fname) && unlink($fname);
            $fp = fopen($fname, 'a');
            file_exists($sjfname) && unlink($sjfname);
            $sjfp = fopen($sjfname, 'a');
        } else {
	        $config = $this->di->get('config')->kafka->loscOrderProducer->toArray();
	        $producer = new Producer($config);
        }

        $loscs = array();
        if($type == 'hour'){
            $time = date('Y-m-d H:i:s', strtotime($before_num . ' hour'));
            $h_time = date('Y-m-d H:00', strtotime($before_num . ' hour'));
            $condition = array(
                "PAYMENT_TIME >" => "'$time'",
                "PAYMENT_STATUS =" => "'PAYED'",
            );
            $select = " distinct LOSC_ID, sem_order.ORDER_ID, ACTUAL_AMOUNT, DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d %H:00') as PAYMENT_TIME";
            $loscs = $this->sem_order->getOrderWithLosc($condition, $select, null);
        }else if($type == 'day'){
            $time = date('Y-m-d', strtotime($before_num . ' day'));
            $after_time = date('Y-m-d', strtotime(($before_num + 1) . ' day'));
            $condition = array(
                "PAYMENT_TIME >= " => "'$time'",
                "PAYMENT_TIME < " => "'$after_time'",
                "PAYMENT_STATUS =" => "'PAYED'",
            );
            $select = " distinct LOSC_ID, sem_order.ORDER_ID, ACTUAL_AMOUNT, DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d') as PAYMENT_TIME";
            $loscs = $this->sem_order->getOrderWithLosc($condition, $select, null);
        }
        if(!$loscs){
            echo $time . ":" . "loscs为空,params:" . json_encode($params) . "\n";
            return;
        }

        //debug模式
        $is_debug = isset($params[2]) ? $params[2] : false;
        if($is_debug){
        	file_exists("/tmp/loscs.txt") && unlink("/tmp/loscs.txt");
            $fp2 = fopen("/tmp/loscs.txt", 'a');
        	foreach ($loscs as $losc){
        		$content = implode("\t", $losc) . "\n";
        		fwrite($fp2, $content);
        	}
            fclose($fp2);
        }

        //查询站内losc
        $losc_ids = array();
        foreach ($loscs as $losc){
            if(!in_array($losc['LOSC_ID'], $losc_ids)){
                $losc_ids[] = $losc['LOSC_ID'];
            }
        }
        $marks = array();
        $step = 1000;
        for($i = 0; $i < count($losc_ids); $i = $i + $step){
            $ids = array_slice($losc_ids, $i, $step);
            $condition = array(
                'CHANNEL_CODE in' =>"('" . implode("','", $ids) . "')",
                'RANGE = ' => "'INNER'",
                'APPLICATION_TYPE = ' => "'losc'",
            );
            $marks = array_merge($marks, $this->ora_mark_channel->getMarkChannelList($condition));
        }

        //debug模式
        if($is_debug){
        	file_exists("/tmp/marks.txt") && unlink("/tmp/marks.txt");
            $fp2 = fopen("/tmp/marks.txt", 'a');
        	foreach ($marks as $mark){
        		$content = implode("\t", $mark) . "\n";
        		fwrite($fp2, $content);
        	}
            fclose($fp2);
        }

        //剔除不符合要求的losc
        $h_loscs = array();
        foreach ($marks as $mark){
            foreach ($loscs as $losc){
                if($mark['CHANNEL_CODE'] == $losc['LOSC_ID']){
                    $h_loscs[] = $losc;
                }
            }
        }
        $loscs = $h_loscs;
        unset($h_loscs);

        //计算金额（应付金额，奖金金额？）
        $amounts = array();
        foreach ($loscs as $losc){
        	$amounts[$losc['ORDER_ID']][] = $losc;
        }
        unset($loscs);

        $groups = array();
        foreach ($amounts as $amount){
            $count = count($amount);
            $count > 4 && $count = 4;
            //losc分成
            $percentage = array(1);
            if($count == 4){
                $percentage = array(0.3, 0.2, 0.2, 0.3);
            }else if($count == 3){
                $percentage = array(0.4, 0.2, 0.4);
            }else if($count == 2){
                $percentage = array(0.5, 0.5);
            }
            foreach ($amount as $k => $a){
                //过滤可能导致金额不准确的数据
                if(isset($h_time) && $h_time == $a['PAYMENT_TIME']){
                    continue;
                }
                if ($k >= 4) {
            		echo 'This losc(' . $a['LOSC_ID'] . ') not share the order(' . $a['ORDER_ID'] . ")!\n";
                	continue;
                }

                $key = $a['LOSC_ID'] . ':' . $a['PAYMENT_TIME'];
                if(!isset($groups[$key])){
	                $groups[$key] = array(
	                		'losc' => $a['LOSC_ID'],
	                		'amount' => 0,
	                		'orderNum' => 0,
	                		'date' => $a['PAYMENT_TIME'],
	                );
                }
                $groups[$key]['amount'] += $a['ACTUAL_AMOUNT'] * $percentage[$k] * 0.01;
                $groups[$key]['orderNum'] ++;
            }
        }
        unset($amounts);

        //写入es_subject
        $this->temp_subject = $this->di->get('cas')->get('temp_subject');
        $sjSql = "select p.subject_id,p.name, p.losc_code,group_concat(s.losc_code) as slosc_code from  sj_template_subject p 
                    left join sj_template_subject s on p.subject_id=s.parent_id and s.status=1
                    where p.parent_id=0 and p.status=1 
                    group by p.subject_id ";
        $subject_info = $this->temp_subject->query($sjSql,'All');
        if(!empty($subject_info)){
            $subjects = array();
            foreach($subject_info as $k=>$sjv){
                $temp['subject_id'] = $sjv['subject_id'] ;
                $temp['subject_name'] = $sjv['name'] ;
                $temp['losc'] = $sjv['losc_code'].((!empty($sjv['slosc_code']))?",".$sjv['slosc_code']:'');
                $losc = explode(",",$temp['losc'])?explode(",",$temp['losc']):array();
                $temp['amount'] = 0;
                $temp['orderNum'] = 0;
                $temp['date'] = $time;
                if(!empty($losc)){
                    foreach($groups as $gk=>$gv){
                        if(in_array($gv['losc'],$losc)){
                            $temp['amount'] += $gv['amount'];
                            $temp['orderNum'] += $gv['orderNum'];
                            $temp['date'] = $gv['date'];
                        }
                    }
                }

                $subjects[$sjv['subject_id']."-".$temp['date']]= $temp;
            }
        }

        //查询losc对应的关键词，推入kafka
        $num = 0;
        $size = 20;
        $count = count($groups);
        $data = array();
        foreach ($groups as $key => $value){
        	$num++;
        	$data[] = $value;
            if(count($data) == $size || $num == $count){
        		if($type == 'hour'){
        			//分时报表推送kafka进行实时处理
        			$producer->sendMsg(json_encode($data));
        			echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
        		}else if($type == 'day'){
        			//分日报表写入文本直接导入到库
        			$content = '';
        			foreach ($data as $d){
        				$content .= implode("\t", $d) . "\n";
        			}
        			fwrite($fp, $content);
        		}
        		$data = array();
        	}
        	usleep(50);
        }
        //subject对应订单
        $sjnum = 0;
        $sjdata = array();
        $count=count($subjects);
        foreach ($subjects as $sk => $sv){
            $sjnum++;
            $sjdata[] = $sv;
            if(count($sjdata) == $size || $sjnum == $count){
                if($type == 'hour'){
                    //分时报表推送kafka进行实时处理
                    echo date('Y-m-d H:i:s', time()) . ":" . json_encode($sjdata) . "\n";
                }else{
                    //分日报表写入文本直接导入到库
                    $content = '';
                    foreach ($sjdata as $d){
                        $content .= implode("\t", $d) . "\n";
                    }
                    fwrite($sjfp, $content);
                }
                $sjdata = array();
            }
            usleep(50);
        }
        if($type == 'day') {
            fclose($fp);
            fclose($sjfp);
        }

    }


    /**
     * 拉取oracle 全部促销及优惠券订单数据
     */
    public function promotionCouponAction($params = array()){
        $this->sem_order = $this->di->get('cas')->get('sem_order_service');
        $this->sem_promotion = $this->di->get('cas')->get('sem_promotion_service');
        $this->sem_coupon = $this->di->get('cas')->get('sem_coupon_service');
        $this->pp_product= $this->di->get('cas')->get('product_pool_product');
        ini_set('memory_limit','256M');

        $type = isset($params[0]) ? $params[0] : 'day';
        if(!in_array($type, array('hour', 'day'))){
            return;
        }
        $before_num = isset($params[1]) ? $params[1] : '-1';
        $before_time = strpos($before_num, "-") <= 0 ? $before_num.' '.$type  : $before_num;

        if ($type == 'day') {
            $time = date('Y-m-d', strtotime($before_time));
            $after_time = date('Y-m-d', strtotime($before_time) + 86400);
        	$fname = APPLICATION_PATH . '/logs/reportdata/coupon.' . date('Y-m-d', strtotime($before_time)) . '.txt';
        	file_exists($fname) && unlink($fname);
            $fp = fopen($fname, 'a');
            $unit_of_time = 1;
        } else {
            $time = date('Y-m-d H:i:s', strtotime($before_time));
            $h_time = date('Y-m-d H:00', strtotime($before_time));
	        $config = $this->di->get('config')->kafka->promotionCouponProducer->toArray();
	        $producer = new Producer($config);
            $unit_of_time = 2;
        }

        //查询促销
        $promotions = array();
        if($type == 'hour'){
            $condition = array(
                "PAYMENT_TIME >" => "'$time'",
            );
            $select = " sem_ord_promotion.PROM_PROMOTION_ID,FAVORABLE_AMOUNT,sem_order.ORDER_ID,DISTRIBUTOR_ID,DISTRIBUTOR_CODE,ACTUAL_AMOUNT,DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d %H:00') AS PAYMENT_TIME,";
            $select .= "ASSUME_DEPT,ASSUME_PERCENT,ASSUME_DEPT2,ASSUME_PERCENT2,ASSUME_DEPT3,ASSUME_PERCENT3,ASSUME_DEPT4,ASSUME_PERCENT4,ASSUME_DEPT5,ASSUME_PERCENT5,PRODUCT_ID";
        }else if($type == 'day'){
            $condition = array(
                "PAYMENT_TIME >=" => "'$time'",
                "PAYMENT_TIME < " => "'$after_time'",
            );
            $select = " sem_ord_promotion.PROM_PROMOTION_ID,FAVORABLE_AMOUNT,sem_order.ORDER_ID,DISTRIBUTOR_ID,DISTRIBUTOR_CODE,ACTUAL_AMOUNT,DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d') AS PAYMENT_TIME,";
            $select .= "ASSUME_DEPT,ASSUME_PERCENT,ASSUME_DEPT2,ASSUME_PERCENT2,ASSUME_DEPT3,ASSUME_PERCENT3,ASSUME_DEPT4,ASSUME_PERCENT4,ASSUME_DEPT5,ASSUME_PERCENT5,PRODUCT_ID";
        }
        $promotions = $this->sem_promotion->getPromotionWithDept($condition, $select, null);
        if($type == 'hour'){
            $condition = array(
            	"ITEM_NAME = " => "'AMOUNT_NAME_PROMOTION'",
            	"ORDER_AMOUNT_TYPE = " => "'PROMOTION_PRICE'",
            	"sem_order.ORDER_ID IS NOT " => "NULL",
            	"PROM_PROMOTION_ID IS " => "NULL",
                "PAYMENT_TIME >" => "'$time'",
            );
            $select = " sem_ord_promotion.PROM_PROMOTION_ID,-ITEM_AMOUNT AS FAVORABLE_AMOUNT,sem_order.ORDER_ID,DISTRIBUTOR_ID,DISTRIBUTOR_CODE,ACTUAL_AMOUNT,DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d %H:00') AS PAYMENT_TIME,";
            $select .= "'无' AS ASSUME_DEPT,100 AS ASSUME_PERCENT,'' AS ASSUME_DEPT2,0 AS ASSUME_PERCENT2,'' AS ASSUME_DEPT3,0 AS ASSUME_PERCENT3,'' AS ASSUME_DEPT4,0 AS ASSUME_PERCENT4,'' AS ASSUME_DEPT5,0 AS ASSUME_PERCENT5,PRODUCT_ID";
        }else if($type == 'day'){
            $condition = array(
            	"ITEM_NAME = " => "'AMOUNT_NAME_PROMOTION'",
            	"ORDER_AMOUNT_TYPE = " => "'PROMOTION_PRICE'",
            	"sem_order.ORDER_ID IS NOT " => "NULL",
            	"PROM_PROMOTION_ID IS " => "NULL",
                "PAYMENT_TIME >=" => "'$time'",
                "PAYMENT_TIME < " => "'$after_time'",
            );
            $select = " sem_ord_promotion.PROM_PROMOTION_ID,-ITEM_AMOUNT AS FAVORABLE_AMOUNT,sem_order.ORDER_ID,DISTRIBUTOR_ID,DISTRIBUTOR_CODE,ACTUAL_AMOUNT,DATE_FORMAT(PAYMENT_TIME,'%Y-%m-%d') AS PAYMENT_TIME,";
            $select .= "'无' AS ASSUME_DEPT,100 AS ASSUME_PERCENT,'' AS ASSUME_DEPT2,0 AS ASSUME_PERCENT2,'' AS ASSUME_DEPT3,0 AS ASSUME_PERCENT3,'' AS ASSUME_DEPT4,0 AS ASSUME_PERCENT4,'' AS ASSUME_DEPT5,0 AS ASSUME_PERCENT5,PRODUCT_ID";
        }
        $promotions = array_merge($promotions, $this->sem_promotion->getPromotionNoDept($condition, $select, null));
        if (empty($promotions)) {
            echo $time . ":" . "promotions为空,params:" . json_encode($params) . "\n";
            $promotions = array();
        }

        //debug模式
        $is_debug = isset($params[2]) ? $params[2] : false;
        if($is_debug){
        	file_exists("/tmp/promotions.txt") && unlink("/tmp/promotions.txt");
            $fp2 = fopen("/tmp/promotions.txt", 'a');
        	foreach ($promotions as $promotion){
        		$content = implode("\t", $promotion) . "\n";
        		fwrite($fp2, $content);
        	}
            fclose($fp2);
        }

        //处理促销数据
        $promotion_coupons = $product_ids = array();
        $order_channels = array('', '驴妈妈前台', '驴妈妈后台 ', '无线APP', '无线WAP', '线下推广');//下单渠道
        foreach ($promotions as $promotion) {
        	$order_channel_id = $this->getOrderChannel($promotion);
        	if (empty($order_channel_id)) continue;
        	for ($i = 1; $i <= 5; $i++) {
        		$assume_dept = $i == 1 ? "ASSUME_DEPT" : "ASSUME_DEPT" . $i;
        		$assume_percent = $i == 1 ? "ASSUME_PERCENT" : "ASSUME_PERCENT" . $i;
        		if (empty($promotion[$assume_dept])) continue;
            	$key = $promotion['PAYMENT_TIME'] . "-" . $promotion[$assume_dept] . "-" . $order_channel_id;
            	if (isset($promotion_coupons[$key])) {
            		$promotion_coupons[$key]['promotion_assume_amount'] += round($promotion['FAVORABLE_AMOUNT'] * $promotion[$assume_percent] / 100 * 0.01, 2);
            		$promotion_coupons[$key]['promotion_order_amount'] += round($promotion['ACTUAL_AMOUNT'] * 0.01, 2);
            	} else {
            		$promotion_coupons[$key] = array(
            			'create_time' => $promotion['PAYMENT_TIME'],
            			'assume_dept_id' => 0,
            			'assume_dept' => $promotion[$assume_dept],
            			'order_channel_id' => $order_channel_id,
            			'order_channel' => $order_channels[$order_channel_id],
            			'promotion_assume_amount' => round($promotion['FAVORABLE_AMOUNT'] * $promotion[$assume_percent] / 100 * 0.01, 2),
            			'promotion_order_amount' => round($promotion['ACTUAL_AMOUNT'] * 0.01, 2),
				        'coupon_assume_amount' => 0,
				        'coupon_order_amount' => 0,
				        'product_id' => $promotion['PRODUCT_ID'],
            		);
            		$product_ids[$promotion['PRODUCT_ID']] = $promotion['PRODUCT_ID'];
            	}
        	}
        }
        unset($promotions, $promotion);

        //查询优惠券
        $coupons = array();
        if($type == 'hour'){
            $condition = array(
                "mark_coupon_usage.create_time >" => "'$time'",
            	"object_type =" => "'VST_ORDER'",
            );
            $select = " coupon_code_id,object_id,amount,DATE_FORMAT(mark_coupon_usage.create_time,'%Y-%m-%d %H:00') AS create_time";
        }else if($type == 'day'){
            $condition = array(
                "mark_coupon_usage.create_time >= " => "'$time'",
                "mark_coupon_usage.create_time < " => "'$after_time'",
            	"object_type =" => "'VST_ORDER'",
            );
            $select = " coupon_code_id,object_id,amount,DATE_FORMAT(mark_coupon_usage.create_time,'%Y-%m-%d') AS create_time";
        }
        $coupons = $this->sem_coupon->getCouponList($condition, null, $select);
        if (empty($coupons)) {
            echo $time . ":" . "coupons为空,params:" . json_encode($params) . "\n";
            $coupons = array();
        }

        //debug模式
        $is_debug = isset($params[2]) ? $params[2] : false;
        if($is_debug){
        	file_exists("/tmp/coupons.txt") && unlink("/tmp/coupons.txt");
            $fp2 = fopen("/tmp/coupons.txt", 'a');
        	foreach ($coupons as $coupon){
        		$content = implode("\t", $coupon) . "\n";
        		fwrite($fp2, $content);
        	}
            fclose($fp2);
        }

        //查询优惠券承担部门
        $coupon_code_arr = $coupon_depts = array();
        foreach ($coupons as $coupon){
        	$table_num = intval(substr($coupon['coupon_code_id'], 1, 4)) % 32;
        	$coupon_code_arr[$table_num][] = $coupon['coupon_code_id'];
        }
        foreach ($coupon_code_arr as $table_num => $coupon_code_ids) {
        	$step = 1000;
        	for($i = 0; $i < count($coupon_code_ids); $i = $i + $step){
        		$ids = array_slice($coupon_code_ids, $i, $step);
        		$sql = "SELECT c.coupon_code_id,d.assume_dept,d.assume_percent from mark_coupon_code_".$table_num." c " .
        			"LEFT JOIN mark_coupon_dept d on d.coupon_id = c.coupon_id where c.coupon_code_id in ('" . implode("','", $ids) . "')";
        		$dept_list = $this->sem_coupon->query($sql,'All');
        		if (empty($dept_list)) continue;
        		foreach ($dept_list as $dept) {
        			$coupon_depts[$dept['coupon_code_id']] = $dept;
        		}
        	}
        }
        unset($coupon_code_arr, $depts_list);

        //查询优惠券关联订单
        $order_ids = $order_keys = $order_coupons = array();
        foreach ($coupons as $coupon){
        	if (isset($coupon_depts[$coupon['coupon_code_id']])) {
        		$coupon['assume_dept'] = $coupon_depts[$coupon['coupon_code_id']]['assume_dept'];
        		$coupon['assume_percent'] = $coupon_depts[$coupon['coupon_code_id']]['assume_percent'];
        	}
        	if(empty($coupon['create_time']) || empty($coupon['assume_dept']) || empty($coupon['object_id'])) continue;
            $key = $coupon['create_time'] . "-" . $coupon['assume_dept'] . "-" . $coupon['object_id'];
            if(!in_array($coupon['object_id'], $order_ids)){
                $order_ids[] = $coupon['object_id'];
                if (empty($order_keys[$coupon['object_id']]) || !in_array($key, $order_keys[$coupon['object_id']])) {
                	$order_keys[$coupon['object_id']][] = $key;
                }
            }
            if (isset($order_coupons[$key])) {
	        	$order_coupons[$key]['assume_amount'] += round($coupon['amount'] * $coupon['assume_percent'] / 100 * 0.01, 2);
            } else {
	            $order_coupons[$key] = array(
	            	'create_time' => $coupon['create_time'],
	            	'assume_dept' => $coupon['assume_dept'],
	            	'assume_amount' => round($coupon['amount'] * $coupon['assume_percent'] / 100 * 0.01, 2),
	            );
            }
        }
        unset($coupons, $coupon);
        $orders = array();
        $step = 1000;
        for($i = 0; $i < count($order_ids); $i = $i + $step){
            $ids = array_slice($order_ids, $i, $step);
            $condition = array(
                'sem_order.ORDER_ID' => " in('" . implode("','", $ids) . "')",
                "PAYMENT_STATUS" => " = 'PAYED'",
                "ORDER_STATUS" => " != 'CANCEL'",
            	'MAIN_ITEM' => " = 'true'",
            );
            $order_list = $this->sem_order->getFullOrderList($condition, "sem_order.ORDER_ID, DISTRIBUTOR_ID, DISTRIBUTOR_CODE, ACTUAL_AMOUNT, PRODUCT_ID", array('order_item'));
            !empty($order_list) && $orders = array_merge($orders, $order_list);
        }
        unset($coupon_depts, $order_ids, $order_list);

        //处理优惠券数据
        foreach ($orders as $order){
        	if (isset($order_keys[$order['ORDER_ID']])) {
        		$order_channel_id = $this->getOrderChannel($order);
        		foreach ($order_keys[$order['ORDER_ID']] as $order_key) {
	        		if (empty($order_channel_id)) {
	        			unset($order_coupons[$order_key]);
	        		} else {
            			$key = $order_coupons[$order_key]['create_time'] . "-" . $order_coupons[$order_key]['assume_dept'] . "-" . $order_channel_id;
            			if (isset($promotion_coupons[$key])) {
		        			$promotion_coupons[$key]['coupon_assume_amount'] += $order_coupons[$order_key]['assume_amount'];
		        			$promotion_coupons[$key]['coupon_order_amount'] += round($order['ACTUAL_AMOUNT'] * 0.01, 2);
            			} else {
	            			$promotion_coupons[$key] = array(
				            	'create_time' => $order_coupons[$order_key]['create_time'],
		            			'assume_dept_id' => 0,
		            			'assume_dept' => $order_coupons[$order_key]['assume_dept'],
		            			'order_channel_id' => $order_channel_id,
		            			'order_channel' => $order_channels[$order_channel_id],
            					'promotion_assume_amount' => 0,
            					'promotion_order_amount' => 0,
				            	'coupon_assume_amount' => $order_coupons[$order_key]['assume_amount'],
				            	'coupon_order_amount' => round($order['ACTUAL_AMOUNT'] * 0.01, 2),
				            	'product_id' => $order['PRODUCT_ID'],
	            			);
            				$product_ids[$order['PRODUCT_ID']] = $order['PRODUCT_ID'];
            			}
	        		}
        		}
        	}
        }
        unset($order_keys, $orders, $order_coupons);
        
        //在另一台数据库获取产品bu
        $products = array();
        $step = 1000;
        for($i = 0; $i < count($product_ids); $i = $i + $step){
            $ids = array_slice($product_ids, $i, $step);
            $condition = array(
                'PRODUCT_ID' => " in('" . implode("','", $ids) . "')",
            );
            $product_list = $this->pp_product->getDefaultList($condition, null, "PRODUCT_ID, BU");
            foreach ($product_list as $product) {
            	$products[$product['PRODUCT_ID']] = $product['BU'];
            }
        }
        unset($product_ids, $product_list);

        //推入kafka或写入txt
        $num = 0;
        $size = 20;
        $count = count($promotion_coupons);
        $data = array();
        foreach ($promotion_coupons as $key => $value){
        	$num++;
        	$value['bu'] = $products[$value['product_id']];
        	$value['unit_of_time'] = $unit_of_time;
        	unset($value['product_id']);
        	$data[] = $value;
            if(count($data) == $size || $num == $count){
        		if($type == 'hour'){
        			//分时报表推送kafka进行实时处理
        			$producer->sendMsg(json_encode($data));
        			echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
        		}else if($type == 'day'){
        			//分日报表写入文本直接导入到库
        			$content = '';
        			foreach ($data as $d){
        				$content .= implode("\t", $d) . "\n";
        			}
        			fwrite($fp, $content);
        		}
        		$data = array();
        	}
        	usleep(50);
        }
        unset($products);
        if($type == 'day') {
            fclose($fp);
        }
    }


    //1:pc  2:mobile
    /*以下来自门票分销组
        DISTRIBUTOR_API(101L, "API 分销渠道"),
		DISTRIBUTOR_B2B(102L, "B2B 渠道"),
		DISTRIBUTOR_DAOMA(104L, "导码分销渠道"),
		DISTRIBUTOR_TAOBAO(106L, "淘宝分销渠道"),
		DISTRIBUTOR_TUANGOU(108L, "团购分销渠道"),
		DISTRIBUTOR_YUYUE(109L, "预约分销渠道"),
		DISTRIBUTOR_LVTU(10000L,"驴途分销"),
		DISTRIBUTOR_TEMAI(107L,"特卖会"),
		DISTRIBUTOR_CPS(103L,"CPS分销"),
		DISTRIBUTOR_LVTUTG(10001L, "驴途团购分销渠道"),
		DISTRIBUTOR_LVTUMS(10002L, "驴途秒杀分销渠道"),
		DISTRIBUTOR_MIAOSHA(110L, "秒杀分销渠道"),
		DISTRIBUTOR_LYQZ(111L,"驴悦亲子渠道");
     */
    private function pcOrMobile($order){
        if($order['DISTRIBUTOR_ID'] == 3){
            return '1';
        }
        if($order['DISTRIBUTOR_ID'] == 4){
            if(in_array($order['DISTRIBUTOR_CODE'], array('DISTRIBUTOR_TUANGOU','DISTRIBUTOR_MIAOSHA','DISTRIBUTOR_TEMAI'))){
                return '1';
            }else if(in_array($order['DISTRIBUTOR_CODE'], array('DISTRIBUTOR_LVTU','DISTRIBUTOR_LVTUTG','DISTRIBUTOR_LVTUMS'))){
                return '2';
            }else{
                $code = explode('_', $order['DISTRIBUTOR_CODE']);
                if($code && isset($code[0]) && in_array(strtolower($code[0]), array('android', 'iphone','ipad', 'touch', 'wp', 'pad', 'weixin'))){
                    return '2';
                }
            }
        }
        return '3';
    }


    /**
     * 获取下单渠道
     * 1:驴妈妈前台  2:驴妈妈后台  3:无线APP 4:无线WAP 5:线下推广
     * 
     * LVMAMA_BACK("2","驴妈妈后台"),
     * LVMAMA_WEB("3","驴妈妈前台"),
     * VST("4","分销商"),
     * XL_CENTER("5","兴旅同业中心 "),
     * SHOP("10","门店"),
     * SHOPAPP("20","门店APP "),
     * AUTO_TB("21","自动购票 ")
     */
    private function getOrderChannel($order){
    	switch ($order['DISTRIBUTOR_ID']) {
    		case 3:  return '1';break;
    		case 2:  return '2';break;
    		case 4:  
	            if(in_array($order['DISTRIBUTOR_CODE'], array('DISTRIBUTOR_TUANGOU','DISTRIBUTOR_MIAOSHA','DISTRIBUTOR_TEMAI'))){
	                return '1';
	            }else if(in_array($order['DISTRIBUTOR_CODE'], array('TOUCH','WAP','TOUCH_LVMM'))){
	                return '4';
	            }else{
	                $code = explode('_', $order['DISTRIBUTOR_CODE']);
	                if($code && isset($code[0]) && in_array(strtolower($code[0]), array('android', 'iphone', 'ipad', 'touch', 'wp', 'wp8', 'lvtu'))){
	                    return '3';
	                }
	            }
	            break;
    		case 6:  return '5';break;
    		default: return '0';
    	}
    	return '0';
    }

}
