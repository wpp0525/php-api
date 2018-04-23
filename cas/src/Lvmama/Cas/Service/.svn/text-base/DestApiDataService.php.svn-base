<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * API 数据服务类
 *
 * @author mac.zhao
 *
 */
class DestApiDataService extends DataServiceBase {

    private $ttl = 43200;

    /**
     * @param $dest_name 目的地名称，也可以为dest_ids
     * @param $type 搜索结果类型：YOULUN：游轮 GROUP：跟团游产品 SCENICTOUR：景+酒产品 FREETOUR：机+酒 TICKET：门票
     * @param $num
     * @return mixed
     */
    public function getProductByDestAndType($dest_name, $type, $num, $forcedb = 1){
        $url ="http://www.lvmama.com/search/getProduct.do?dest1=".$dest_name."&num=".$num."&type=".$type;
        if($forcedb){
            $result = $this->curl($url);
        }else{
            $key = 'srv:getProductByDestAndType:' . ':' . $dest_name . ':' . $type . ':' . $num;
            $result = $this->redis->get($key);
            if(!$result){
                $result = $this->curl($url);
                $this->redis->set($key, $result);
                $this->redis->expire($key, $this->ttl);
            }
        }
        return $result;
    }

    private function curl($url, $method='GET', $postfields = array(), $headers = array()) {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, 'CAS API SERVICE' . ' '.  '1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'PUT':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
                }
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            default:
                curl_setopt($ci, CURLOPT_POST, FALSE);
                if (!empty($postfields)) {
                    $url = $url."?".http_build_query($postfields);
                }
        }

        if (!empty($this->remote_ip)) {
            $headers['ApiRemoteAddr'] = $this->remote_ip;
        } elseif (($remote_addr = Misc::getclientip()) != 'unknown') {
            $headers['ApiRemoteAddr'] = $remote_addr;
        }

         curl_setopt($ci, CURLOPT_URL, $url );
         curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
         curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
         $res = curl_exec($ci);
         curl_close ($ci);
         return $res;
    }
}