<?php

namespace Lvmama\Cas\Component\Kafka;

    /**
     * Class Producer
     *
     * @author zhaiyuansen
     * @package Lvmama\Cas\Component\Kafka
     */
class Producer{

    /**
     * @var \RdKafka\Conf
     */
    protected $conf;

    /**
     * @var \RdKafka\TopicConf
     */
    protected $topicConf;

    /**
     * @var KafkaProducer
     */
    protected $KafkaProducer;

    /**
     * @var TopicList
     */
    protected $topicList;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var boolean
     */
    protected $running = true;

    public function __construct($config = NULL){

        $this->conf = new \RdKafka\Conf();

        $this->topicConf = new \RdKafka\TopicConf();

        $this->KafkaProducer = new \RdKafka\Producer();

        $this->conf->setDrMsgCb(function (\RdKafka\Producer $kafka, $message){
            if($message->err){
                echo $message->err.' : ';
                echo 'Producer failed';
            }else{
                echo 'Producer success';
            }
        });

        if($config['brokerList'] == '')
            $config['brokerList'] = "10.112.4.17";

        $this->KafkaProducer->setLogLevel(LOG_DEBUG);

        $this->KafkaProducer->addBrokers($config['brokerList']);

        if($config['topics'])
            $this->client = $this->KafkaProducer->newTopic($config['topics'], $this->topicConf);

//        $rk->setLogLevel(LOG_DEBUG);
//        $rk->addBrokers("10.112.4.17");
//        $topic = $rk->newTopic("test1", $this->topicConf);
//        $topic->produce(RD_KAFKA_PARTITION_UA, 0, 'message 1');

//        for ($i = 0; $i < 10; $i++) {
//            $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Text $i");
//            $rk->poll(0);
//        }
//
//        while ($rk->getOutQLen() > 0) {
//            $rk->poll(50);
//        }

    }

    /**
     * RdKafka::setLogLevel — Set log level
     * @params $log_level
     * return void
     */
    public function setLogLevel($log_level){
        $this->KafkaProducer->setLogLevel($log_level);
    }

    /**
     * RdKafka::getOutQLen — Get the out queue length
     * return void
     */
    public function getOutQLen(){
        return $this->KafkaProducer->getOutQLen();
    }

    /**
     * RdKafka::getMetadata — Request Metadata from broker
     * @params boolean $all_topic When TRUE, request info about all topics in cluster. Else, only request info about locally known topics.
     * @params Class \RdKafka\Topic $only_topic When non-null, only request info about this topic
     * @params int $timeout_ms Timeout (milliseconds)
     * Returns a RdKafka\Metadata instance
     */
    public function getMetadata(bool $all_topics, \RdKafka\Topic $only_topic = NULL, $timeout_ms){
        return $this->KafkaProducer->getMetadata($all_topics, $only_topic, $timeout_ms);
    }

    /**
     * RdKafka::newQueue — Create a new message queue instance
     * Returns a RdKafka\Queue.
     */
    public function newQueue(){
        return $this->KafkaProducer->newQueue();
    }

    /**
     * RdKafka::poll — Poll for events
     * Returns the number of events served.
     */
    public function poll($timeout_ms = 0){
        return $this->KafkaProducer->poll($timeout_ms);
    }

    /**
     * RdKafka::addBrokers — Add brokers
     * @params string $broker_list
     * Returns the number of brokers successfully added.
     */
    public function addBrokers(string $broker_list){
        $this->KafkaProducer->addBrokers($broker_list);
    }

    /**
     * RdKafka::newTopic — Create a new topic instance
     * @params string $topic_name
     * @params Class RdKafka\TopicConf $topic_conf
     * Returns a RdKafka\Topic (more specifically, either a RdKafka\ConsumerTopic or a RdKafka\ProducerTopic).
     */
    public function newTopic(string $topic_name, \RdKafka\TopicConf $topic_conf = NULL){
        $this->KafkaProducer->newTopic($topic_name, $topic_conf);
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    ////////////////////////////////////////////以下自行实现/////////////////////////////////////

    /**
     * Producer::sendMsg($msg) 发送消息
     * @params string $msg 消息体内容
     */
    public function sendMsg($msg){
        $this->client->produce(RD_KAFKA_PARTITION_UA, 0, $msg);
    }

}