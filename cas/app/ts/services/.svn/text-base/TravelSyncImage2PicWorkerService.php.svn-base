<?php

use Lvmama\Cas\Component\DaemonServiceInterface,
    Lvmama\Cas\Service\RedisDataService,
    Lvmama\Common\Utils\Misc,
    Lvmama\Cas\Service\BeanstalkDataService;

class TravelSyncImage2PicWorkerService implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $flag_id;
    private $redis;
    private $beanstalk;
    private $redis_key;
    private $upload_url;

    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->redis = $di->get('cas')->getRedis();

        $this->redis_key = RedisDataService::REDIS_TRAVEL_UPLOAD_IMAGE_LIST;

        $this->beanstalk = $di->get('cas')->getBeanstalk();

        $this->upload_url = 'http://piclib.lvmama.com/photo-back/photo/photo/uploadImgAndSave.do';
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null)
    {
        $this->flag_id = $flag;
        $this->syncImage();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    /**
     * 同步图片
     */
    private function syncImage()
    {
        $curr_job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRAVEL_IMAGE_UPLOAD_LIST)->ignore('default')->reserve();
        if ($curr_job) {
            try {
                $job_data = json_decode($curr_job->getData(), true);
                if ($job_data) {
                    $redis_data = $this->getRedisData($job_data['image_id']);
                    if ($redis_data) {
                        switch ($redis_data['action']) {
                            case 'rotate' :
                                $job_data['image_url'] = $redis_data['image_url'];
                                $job_data['imagePath'] = $redis_data['path'];
                                $this->uploadImage($curr_job, $job_data);
                                break;
                            case 'delete' :
                            default:
                                $this->beanstalk->delete($curr_job);
                                $this->delRedisData($job_data['image_id']);
                                break;
                        }
                    } else
                        $this->uploadImage($curr_job, $job_data);
                }
                unset($job_data);
            } catch (\Exception $ex) {
                echo $ex->getMessage() . ',' . $ex->getTraceAsString() . '\r\n';
            }
        }
        unset($curr_job);
    }

    /**
     * 上传图片
     * @param $curr_job
     * @param $job_data
     */
    private function uploadImage($curr_job, $job_data)
    {
        $upload_result = $this->uploadImage2Pic($job_data);
        if ($upload_result) {
            $update_res = $this->updateImageUrl($upload_result, $job_data['image_id']);
            if ($update_res) {
                $this->beanstalk->delete($curr_job);
                $this->delRedisData($job_data['image_id']);
            }
        }
    }

    /**
     * 取 REDIS 数据
     * @param int $image_id
     * @return mixed
     */
    private function getRedisData($image_id = 0)
    {
        return $this->redis->hgetall($this->getFullRedisKey($image_id));
    }

    /**
     * 删除 REDIS 数据
     * @param int $image_id
     * @return mixed
     */
    private function delRedisData($image_id = 0)
    {
        return $this->redis->del($this->getFullRedisKey($image_id));
    }

    /**
     * 返回替换后的 REDIS 键
     * @param int $image_id
     * @return mixed
     */
    private function getFullRedisKey($image_id = 0)
    {
        return str_replace('{image_id}', $image_id, $this->redis_key);
    }

    /**
     * 将图库的图片路径写入数据库
     * @param string $image_url
     * @param int $img_id
     * @return bool
     */
    private function updateImageUrl($image_url = '', $img_id = 0)
    {
        $res = $this->traveldatasvc->update(array(
            'table' => 'image',
            'where' => "`id` = '{$img_id}'",
            'data' => array(
                'pic_url' => $image_url,
            ),
        ));
        if (!$res['error'])
            return true;
        return false;
    }

    /**
     * 上传图片至图库
     * @param array $post_fields
     * @return bool
     */
    private function uploadImage2Pic($post_fields = array())
    {
        $result = $this->curl($this->upload_url, 'POST', $post_fields);
        if (isset($result['success']) && $result['success'])
            return $result['url'];
        return false;
    }

    private function curl($url, $method = 'GET', $postfields = array(), $headers = array())
    {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, 'CAS API SERVICE' . ' ' . '1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    $postfields['imagePath'] = new CURLFile($postfields['imagePath']);
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
                    $url = $url . "?" . http_build_query($postfields);
                }
        }

        if (!empty($this->remote_ip)) {
            $headers['ApiRemoteAddr'] = $this->remote_ip;
        } elseif (($remote_addr = Misc::getclientip()) != 'unknown') {
            $headers['ApiRemoteAddr'] = $remote_addr;
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $string = curl_exec($ci);
        curl_close($ci);
        //过滤非标准json格式字符
        $aBP = strpos($string, '[');//数组符号第一个位置
        $oBP = strpos($string, '{');//对象符号第一个位置
        //如果都不存在有这两个符号，表示非json数据，直接返回原始数据
        if ($aBP === false && $oBP === false) {
            $data = $string;
        } else {
            $aEP = strrpos($string, ']');//数组符号最后一个位置
            $oEP = strrpos($string, '}');//对象符号最后一个位置
            //否则,如果只存在{，那么只返回对象部分数据
            if ($aBP === false) {
                $jsonData = substr($string, $oBP, ($oEP - $oBP + 1));
            } elseif ($oBP === false) {
                //如果只存在[,那么只返回数组部分数据
                $jsonData = substr($string, $aBP, ($aEP - $aBP + 1));
            } else {
                //[和{都存在，那么比较位置大小，取值最小的
                $bP = min($aBP, $oBP);
                $eP = ($bP == $aBP) ? $aEP : $oEP;
                $jsonData = substr($string, $bP, ($eP - $bP + 1));
            }
            $data = json_decode($jsonData, true);
            //超时或者无效接口，直接返回错误信息
            if (isset($data['error']) && $data['error'] && isset($data['status']) && !$data['status']) return array('error' => 'api timeout error');
            //判断是否json数据,非json数据，返回获取到的字符串
            if ($data === null) $data = $string;
        }
        return $data;
    }
}