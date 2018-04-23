<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use \Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Cas\Component\Kafka\Consumer;

/**
 * ip定位
 *
 * @author libiying
 *
 */
class IplocationTask extends Task {

    /**
     *
     * @var \Phalcon\DiInterface
     */
    private $di;

    /**
     * @var \Lvmama\Cas\Service\RedisDataService
     */
    private $redis;

    /**
     * @var \Lvmama\Cas\Service\DistBaseIpService
     */
    private $distIp;

    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $destBase;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );

        $this->di = $dependencyInjector;

        $this->redis = $dependencyInjector->get('cas')->get('redis_data_service');
        $this->distIp = $dependencyInjector->get('cas')->get('dist_base_ip_service');
        $this->destBase = $dependencyInjector->get('cas')->get('destin_base_service');
    }

    /**
     * @example php ts.php Iplocation ipDistrict2Redis
     */
    public function ipDistrict2RedisAction(){

        $key = 'zset:dist:ip:20170413';
        $num = 0;
        $size = 1000;
        $total = $this->distIp->getDistTotal(array());
        $count = 0;

        while ($num * $size < $total) {
            $ips = $this->distIp->getDistList(array(), ($num * $size) . ',' . $size);

            foreach ($ips as $ip) {
                $this->redis->dataZAdd($key, $ip['StartIPNum'], $ip['district_id'] . '_' . $ip['StartIPNum']);
                $count ++;
            }
            $num ++;
            echo '已导入：' . $count . "\n";
        }
        echo 'iplocation导入redis完毕，共' . $count . '条数据' . "\n";
    }

    /**
     * @example php ts.php Iplocation districtInfo2Redis
     */
    public function districtInfo2RedisAction(){

//        $key_comcity = 'hash:comcity:';
//        $comcitys = $this->destBase->getList(array("district_id <>" => 0), 'dest_com_city');
//        foreach ($comcitys as $comcity){
//            $this->redis->dataHmset($key_comcity . $comcity['district_id'], $comcity, null);
//        }
//        echo 'comcity导入redis完毕，共' . count($comcitys) . '条数据' . "\n";

        $key_prefix = 'hash:dist:';
        $keys = array('dest_id','dest_type','dest_name','district_id','parent_district_id','city_id','city_name','province_id','province_name');

        $num = 0;
        $size = 1000;
        $count = 0;

        $infos = $this->destBase->getDistrictInfo($num . ',' . $size);

        while ($infos){
            foreach ($infos as $info){
                $this->redis->dataHmset($key_prefix . $info['district_id'], $info, null);
                $count ++;
            }
            $num ++;
            $infos = $this->destBase->getDistrictInfo(($num * $size) . ',' . $size);
            echo '已导入：' . $count . "\n";
        }
        echo 'dist导入redis完毕，共' . $count . '条数据' . "\n";
    }


    /**
     * @example php ts.php Iplocation districtInfo2Redis2
     */
    public function districtInfo22RedisAction(){

        $key_prefix = 'hash:dist:province:index';

        $num = 0;
        $size = 1000;
        $count = 0;

        $infos = $this->destBase->getDistrictInfo2($num . ',' . $size);

        while ($infos){

            $provinceArr = array();
            foreach ($infos as $info){
                if( empty($info['province_name']) || empty($info['district_id']) ){
                        continue;
                }
                $provinceArr += array( $info['province_name'] => $info['district_id']);
                $count ++;
            }
            $provinceArr += array( "港澳台及境外" => "other");
            var_dump($provinceArr);
//            exit;
            $this->redis->dataHmset($key_prefix, $provinceArr, null);
            $num ++;
            $infos = $this->destBase->getDistrictInfo2(($num * $size) . ',' . $size);
            echo '已导入：' . $count . PHP_EOL;
        }
        echo 'dist导入redis完毕，共' . $count . '条数据' . PHP_EOL;
    }


    /**
     * 门票产品的信息生成索引
     * @example php ts.php Iplocation productInfoIndexRedis
     */
    public function productInfoIndex2RedisAction(){

        $key_prefix = 'hash:dist:province:index';
        $zkey = "goodslib:province:ticket:";

        $size = 100;

        $categoryIds = array(5,11,12,13);
        $provinceDatas = $this->redis->dataHgetall($key_prefix);

        $sum = 0;
        foreach ($provinceDatas  as $provinceId => $provinceName){
            $page = 0;
            $score = 0;
           $ticketKey = $zkey . $provinceId;
           $this->redis->dataDelete($ticketKey); //重新写入之前删除原来的数据

           $infos = $this->destBase->getProvinceProductInfo($page . ',' . $size, $provinceId, $categoryIds );
           while ($infos) {

               foreach ($infos as $info) {
                   $this->redis->dataZAdd($ticketKey, $score++, $info['product_id']);
               }
               echo "省份id为:" . $provinceId . "，省份为: " . $provinceName . ',正在导入的数据为：' . $score . PHP_EOL;
               $page++;
               $infos = $this->destBase->getProvinceProductInfo(($page * $size) . ',' . $size, $provinceId, $categoryIds );
           }
           $sum += $score;
           echo "省份id为:" . $provinceId . "，省份为: " . $provinceName . ',已导入数据为：' . $score . PHP_EOL;
        }

        echo  '所有省份导入的数据为：' . $sum .PHP_EOL;

    }

}
