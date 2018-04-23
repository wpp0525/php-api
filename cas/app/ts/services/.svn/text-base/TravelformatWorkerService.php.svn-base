<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-9-28
 * Time: 下午4:50
 */

use Lvmama\Cas\Component\DaemonServiceInterface;

class TravelformatWorkerService implements DaemonServiceInterface {

    private $traveldatasvc;

    private $trtcdsvc;

    public function __construct($di) {

        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->trtcdsvc = $di->get('cas')->get('tr-travel-content-data-service');
        $this->trtcdsvc->setReconnect(true);

    }
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function Process($timestamp = null, $flag = null) {
    }

    /**
     * 回答审核后更新question zero list
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function processFormatContent($timestamp = null, $flag = null) {

        //新游记分界 `travel_id` >= 95664 max = 267872     172208 181768
        // `travel_id` > 181768
        $result = $this->trtcdsvc->query("SELECT COUNT(*) AS itemCount FROM `tr_travel_content` WHERE `content` != '' AND `travel_id` >= 95664 AND `travel_id` <= 181768");
        $total = $result['itemCount'];
        $total_page = ceil( $total / 50 );
        // echo $total_page;
        $this->excuteFormatData(1, 50, $total_page);

    }

    private function excuteFormatData($page_num = 1, $page_size = 50, $total_page){
        if($page_num <= $total_page){

            $begin = ($page_num - 1) * $page_size;

            $importImg = true;

            echo 'page '.$page_num." begin at ".date('Y-m-d H:i:s',time())."; \r\n";
            $res_array = $this->trtcdsvc->query("SELECT id,travel_id,content FROM `tr_travel_content` WHERE `content` != '' AND `travel_id` >= 95664 AND `travel_id` <= 181768 ORDER BY id ASC LIMIT {$begin}, {$page_size}", 'All');
            if(is_array($res_array) && $res_array){
                foreach($res_array as $key => $val){

                    // echo $key.'==='.$val['id']."\r\n";

                    $val['content'] = preg_replace("/<a[^>]*>/", "", $val['content']);
                    $val['content'] = preg_replace("/<\/a>/", "", $val['content']);

                    /*
                    $pattern1 = "/<[img|IMG].*?src=[\'|\"](http\:\/\/|ftp\:\/\/|https\:\/\/)(.*?[^\/])\/(.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
                    $pattern2 = "/<[img|IMG].*?src=(http\:\/\/|ftp\:\/\/|https\:\/\/)(.*?[^\/])\/(.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp])) .*?[\/]?>/";
                    */
                    $pattern1 = "/<[img|IMG].*?src=[\'|\"](http\:\/\/|ftp\:\/\/|https\:\/\/)(.*?[^\/])\/(.*?)[\'|\"].*?[\/]?>/";
                    $pattern2 = "/<[img|IMG].*?src=(http\:\/\/|ftp\:\/\/|https\:\/\/)(.*?[^\/])\/(.*?) .*?[\/]?>/";

                    preg_match_all($pattern1, $val['content'], $match_url1);
                    preg_match_all($pattern2, $val['content'], $match_url2);
                    $match_url1[3] = str_replace('_720_', '', $match_url1[3]);
                    $match_url2[3] = str_replace('_720_', '', $match_url2[3]);

                    $imgInDb = "uploads/pc/place2/";
                    $url_array = array();

                    $travel_id = $val['travel_id'];
                    $travel_content_id = $val['id'];

                    if($match_url1[3] && is_array($match_url1[3])){
                        foreach($match_url1[3] as $k_1 => $v_1){
                            $image_url = '';
                            if(strpos($v_1, $imgInDb)){
                                $url_array[] = $v_1;
                            }else{
                                if($importImg){
                                    $image_url = $match_url1[1][$k_1].$match_url1[2][$k_1].'/'.$match_url1[3][$k_1];
                                    // echo $image_url."\r\n";
                                    $res = $this->saveImage($image_url, $travel_id, $travel_content_id);
                                    // var_dump($res);
                                    if($res['code'] == 200 && $res['data']){
                                        $new_url = "http://pic.lvmama.com/".$res['data'];
                                        // echo $new_url."\r\n";
                                        $val['content'] = str_replace($image_url, $new_url, $val['content']);
                                        // echo $val['content']."\r\n"; die;
                                    }
                                }
                            }
                        }
                    }

                    if($match_url2[3] && is_array($match_url2[3])){
                        foreach($match_url2[3] as $k_2 => $v_2){
                            $image_url = '';
                            if(strpos($v_2, $imgInDb)){
                                $url_array[] = $v_2;
                            }else{
                                if($importImg){
                                    $image_url = $match_url2[1][$k_2].$match_url2[2][$k_2].'/'.$match_url2[3][$k_2];
                                    // echo $image_url."\r\n";
                                    $res = $this->saveImage($image_url, $travel_id, $travel_content_id);
                                    // var_dump($res);
                                    if($res['code'] == 200 && $res['data']){
                                        $new_url = "http://pic.lvmama.com/".$res['data'];
                                        // echo $new_url."\r\n";
                                        $val['content'] = str_replace($image_url, $new_url, $val['content']);
                                        // echo $val['content']."\r\n"; die;
                                    }
                                }
                            }
                        }
                    }

                    if($url_array){
                        $img_url_str = implode("', '", $url_array);
                        $count_img = count($url_array);
                        $imgInfo = $this->trtcdsvc->query("SELECT id FROM `tr_image` WHERE `url` IN ('{$img_url_str}') LIMIT {$count_img}", 'All');
                        // 按 image_id 修复 tr_travel_image_rel 中的 travel_id travel_content_id
                        $imgArr = array();
                        if(is_array($imgInfo)){
                            foreach($imgInfo as $imgLine){
                                if($imgLine['id']){
                                    $imgArr[] = $imgLine['id'];
                                }
                            }
                            if($imgArr){
                                $imgId_str = implode("', '", $imgArr);
                                $this->trtcdsvc->query("UPDATE `tr_travel_image_rel` SET `travel_content_id` = {$val['id']} WHERE `image_id` IN ('{$imgId_str}') AND  `travel_id` = {$val['travel_id']};");
                            }
                        }
                    }

                    // echo $val['content']."\r\n";
                    $up_data = array();
                    $up_data['content'] = $val['content'];
                    $travel_update_data = array('table' => 'travel_content', 'data' => $up_data);
                    $travel_update_data['where'] = "id = {$val['id']}";
                    $this->traveldatasvc->update($travel_update_data);
                    // var_dump($a); die;
                    // $a = $this->trtcdsvc->query("UPDATE `tr_travel_content` SET `content` = '{$val['content']}' WHERE id = {$val['id']}");
                    // echo $a."\r\n"; die;
                }
            }

            echo 'page '.$page_num." end at ".date('Y-m-d H:i:s',time())."; \r\n";

            $current_page = $page_num + 1;
            $result = $this->trtcdsvc->query("SELECT COUNT(*) AS itemCount FROM `tr_travel_content` WHERE `content` != '' AND `travel_id` >= 95664 AND `travel_id` <= 181768");
            $total = $result['itemCount'];
            $total_page = ceil( $total / 50 );

            sleep(3);
            $this->excuteFormatData($current_page, $page_size, $total_page);
        }else{
            echo 'job done';
            exit;
        }
    }

    /**
     * 保存远程图片
     * @param string $image_url
     * @param int $travel_id
     * @param int $travel_content_id
     * @return array
     */
    private function saveImage($image_url = '',$travel_id = 0, $travel_content_id = 0){
        $image_string = $this->curl($image_url);
        $file_path = '/tmp/' . time() . '.jpg';
        $res = file_put_contents($file_path,$image_string);
        if(!$res)
            return array('error' => 1,'message' => 'save faild');

        $upload_res = $this->UploadImage2Pic($file_path,$travel_id,$travel_content_id);
        if($upload_res['error'])
            return array('code' =>'100000','msg' => '失败','data' => $upload_res['msg']);
        return array('code' => '200','msg' => '成功','data' => $upload_res['data']);
    }

    /**
     * 将图片上传到图库
     * @param string $image_path
     * @param int $travel_id
     * @param int $travel_content_id
     * @return array|string
     */
    private function UploadImage2Pic($image_path = '',$travel_id = 0,$travel_content_id = 0){
        $java_upload_url = 'http://piclib.lvmama.com/photo-back/photo/photo/uploadImgAndSave.do';
        $post_fields = array(
            'photoType' => "trip",
            'photoSecondType' => "trip",
            'photoName' => $travel_id,
            'photoCopyrightId' => '162',
            'isPHP_PC' => 'true',
            'photoAutoCut' => 1,
            'imagePath' => $image_path,
        );
        list($width, $height, $type, $attr) = getimagesize($image_path);
        $result = $this->curl($java_upload_url, 'POST', $post_fields);

        if (isset($result['success']) && $result['success']) {
            $image_result = array('error' => 1);
            $dest_rel_params = array(
                'table' => 'travel_content_dest_rel',
                'select' => 'dest_id',
                'where' => array('travel_content_id' => $travel_content_id,'travel_id' => $travel_id),
            );
            $image_result = $this->traveldatasvc->select($dest_rel_params);
            if(!isset($image_result["list"]) || is_null($image_result["list"]['0']['dest_id']))
                return json_encode(array("error" => "1", 'msg' => 'select dest_id faild'));

            $dest_id = $image_result["list"]['0']['dest_id'];

            $image_params = array(
                'table' => 'image',
                'data' => array(
                    'dest_id' => $dest_id,
                    'url' => $result['url'],
                    'pic_url' => $result['url'],
                    'width' => $width,
                    'create_time' => time(),
                    'update_time' => time(),
                ),
            );
            $image_result = $this->traveldatasvc->insert($image_params);
            // 返回数据
            if ($image_result['error'])
                return array("error" => "1", 'msg' => 'insert image faild');

            $image_rel_params = array(
                'table' => 'travel_image_rel',
                'data' => array(
                    'travel_id' => $travel_id,
                    'travel_content_id' => $travel_content_id,
                    'image_id' => $image_result['result'],
                ),
            );
            $image_result = $this->traveldatasvc->insert($image_rel_params);

            // 返回数据
            if ($image_result['error'])
                return array("error" => "1", 'msg' => 'insert image_rel faild');

            return array('error' => '0','msg' => 'SUCCESS','data' => $result['url']);
        }
        return array("error" => "1", 'msg' => 'upload image to pic faild');
    }

    private function curl($url, $method = 'GET', $postfields = array(), $headers = array()) {
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
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

}
