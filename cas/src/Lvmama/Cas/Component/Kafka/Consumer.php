<?php
namespace Lvmama\Cas\Component\Kafka;
use Pheanstalk\Exception;

/**
 * Class Consumer
 *
 * @author libiying
 * @package Lvmama\Cas\Component\Kafka
 */
class Consumer{

    /**
     * @var \RdKafka\Conf
     */
    protected $conf;

    /**
     * @var \RdKafka\TopicConf
     */
    protected $topicConf;

    /**
     * @var \RdKafka\KafkaConsumer
     */
    protected $kafkaConsumer;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var boolean
     */
    protected $running = true;

    public function __construct($config){

        $this->topicConf = new \RdKafka\TopicConf();
        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $this->topicConf->set('auto.offset.reset', 'largest');

        $this->conf = new \RdKafka\Conf();
        $this->conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });
        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $this->conf->set('group.id', $config['groupId']);
        // Initial list of Kafka brokers
        $this->conf->set('metadata.broker.list', $config['brokerList']);
        // Set the configuration to use for subscribed/assigned topics
        $this->conf->setDefaultTopicConf($this->topicConf);

        $this->kafkaConsumer = new \RdKafka\KafkaConsumer($this->conf);

        $this->kafkaConsumer->subscribe($config['topics']);
    }

    public function run(){

        if(!$this->client){
            throw new \Exception("Client can not be null!");
        }

        while($this->running){
            $message = $this->kafkaConsumer->consume(120*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try{
                        $this->client->handle($message);
                    }catch (\Exception $ee){
                        echo date('Y-m-d H:i:s') . ",error," . $ee->getCode() . ":" . $ee->getMessage() . "\n";
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    $this->client->error();
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $this->client->timeOut();
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    public function stop(){
        $this->running = false;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}