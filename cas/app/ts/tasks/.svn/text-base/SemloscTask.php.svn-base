<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use \Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Cas\Component\Kafka\Consumer;

/**
 * 百度搜索接口定时任务
 *
 * @author libiying
 *
 */
class SemloscTask extends Task {

    /**
     *
     * @var \Phalcon\DiInterface
     */
    private $di;

    /**
     * @var \Lvmama\Cas\Service\SemKeywordBaseDataService
     */
    private $keyword;

    /**
     * @var \Lvmama\Cas\Service\RedisDataService;
     */
    private $redis;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );

        $this->di = $dependencyInjector;
        $this->keyword = $dependencyInjector->get('cas')->get('sem_keyword_service');
        $this->redis = $dependencyInjector->get('cas')->get('redis_data_service');
//        echo 333;die;
    }


    /**
     * 准备好new losc 暂存keyword
     *  参数1：类型 makeNew：生成新losc；replaceOld：替换旧的
     *  参数2：用户id
     * @param $params
     */
    public function putKeyword2KafkaAction($params){

        $type = isset($params[0]) ? $params[0] : null;
        $userId = isset($params[1]) ? explode(',', $params[1]) : null;
        $adgroupId = isset($params[2]) ? explode(',', $params[2]) : null;
        $keywordId = isset($params[3]) ? explode(',', $params[3]) : null;

        if(!$type || !in_array($type, array('makeNew', 'replaceOld')) || !$userId){
            die("参数错误！");
        }

        $config = $this->di->get('config')->kafka->semNewLoscProducer->toArray();
        $producer = new Producer($config);

        $condition = array();
        if($userId){
            $condition['userId in'] = "(" . implode(',', $userId) . ")";
        }
        if($adgroupId){
            $condition['adgroupId in'] = "(" . implode(',', $adgroupId) . ")";
        }
        if($keywordId){
            $condition['keywordId in'] = "(" . implode(',', $keywordId) . ")";
        }
        if($type == 'makeNew'){
            $condition['new_losc'] = " is null ";
        }else if($type == 'replaceOld'){
            $condition['new_losc '] = 'is not null';
            $condition['new_losc <>'] = "''";
            $condition['new_losc <> '] = 'losc';
        }
        $total = $this->keyword->getKeywordTotal($condition);

        $num = 0;
        $size = 1000;
        $batch = 20;
        $batch_num = 0;
        $kws = array();
        for ($page = 0; $total > $page * $size; $page++){
            $limit = ($page * $size). ',' . $size;
            $keywords = $this->keyword->getKeywordList($condition, $limit, 'userId, keywordId, keyword, pcDestinationUrl, mobileDestinationUrl, losc, new_losc');

            $keywords_count = count($keywords);
            foreach ($keywords as $keyword){

                $batch_num ++;
                $kws[] = $keyword;
                if(count($kws) >= $batch || $batch_num == $keywords_count){
                    $data = array(
                        'type' => $type,
                        'keywords' => $kws,
                    );
                    $producer->sendMsg(json_encode($data));
                    echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
                    echo date('Y-m-d H:i:s', time()) . " memory_get_usage：" . memory_get_usage() . "\n";

                    $kws = array();
                }
                $num ++;
            }
            $batch_num = 0;
        }
        echo date('Y-m-d H:i:s', time()) . " 共推送：" . $num . "条数据\n";
    }
    
    public function semNewLoscAction(){

        $config = $this->getDI ()->get ( 'config' )->kafka->semNewLoscConsumer->toArray ();
        $consumer = new Consumer($config);
        $consumer->setClient(new SemNewLoscWorkerService($this->di));
        $consumer->run();
        return;
    }

    public function createLoscAction($params){
        $this->client = $this->di->get('tsrv');

        $filename = isset($params[0]) ? $params[0] : '';
        $saveFilename = isset($params[1]) ? $params[1] : '';

        $file = fopen($filename, "r") or die("Unable to open file!\n");;
        $content = fread($file, filesize($filename));
        fclose($file);

        $saveFile = fopen($saveFilename, "a") or die("unable to open saveFile!\n");

        $keywords = array();
        $content = explode("\r\n", $content);
        $head = explode(",", $content[0]);
        unset($content[0]);
        foreach ($content as $ct){
            $keyword = array();
            $c = explode(",", $ct);
            foreach ($head as $key => $value){
                $keyword[$value] = $c[$key];
            }
            $keywords[] = $keyword;
        }

        fwrite($saveFile, implode(",", $head) . ",losc\n");
        foreach ($keywords as $key => $keyword){
            $params = array(
                'code1' => $keyword['code1'],
                'code2' => $keyword['code2'],
                'name2' => $keyword['name2'],
                'name3' => $keyword['name3'],
                'channelComment' => "来自脚本批量创建",
            );
            $res = $this->client->exec("order/autoCreateChannel", array('params' => json_encode($params)));
            if($res){
                $keyword['losc'] = $res['msg'];
            }else{
                $keyword['losc'] = 0;
            }

            fwrite($saveFile, implode(",", $keyword) . "\n");
        }

        fclose($saveFile);
    }
}
