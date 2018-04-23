<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地详细数据 服务类
 */
class VstDestSumaryDataService extends DataServiceBase{
    /**
     * @purpose 插入数据
     * @param $data   数据
     * @param $table_name  详情表表名
     * @return array
     * @throws \Exception
     */
    public function insert($data,$table_name){
        $table_name=$this->dest_type?$this->dest_type:$table_name;
        $is_exist=$this->isTableExist($table_name);
        if($is_exist){
            if($id = $this->getAdapter()->insert($table_name, array_values($data), array_keys($data)) ){

            }
            $result = array('error'=>0, 'result'=>$id);
            return $result;
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }

    /**
     * 获取目的地建议游玩时间
     * @param $dest_id
     * @return bool|mixed
     */
    public function getSuggestTimeByDestId($dest_id){
        $table_name='ly_suggest_time';
        $sql="SELECT * FROM ".$table_name." WHERE `status`=99 AND dest_id=".$dest_id;
        return $this->query($sql);
    }

    /**
     * 获取目的地指南里的TEXT类型简单数据集合
     * @param $dest_id
     * @return bool|mixed
     */
    public function getDestTextData($dest_id){
        $sql="SELECT * FROM ly_data WHERE `status`=99 AND data_type='TEXT' AND dest_id=".$dest_id;
        return $this->query($sql,'All');
    }

    /**
     * 获取目的地交通信息
     * @param $dest_id
     * @param string $type
     * @return bool|mixed
     */
    public function getDestTransport($dest_id,$type='POI'){
        $sql="SELECT * FROM ly_transportation WHERE `status`=99 AND type='".$type."' AND dest_id=".$dest_id." ORDER BY seq ASC";
        return $this->query($sql,'All');
    }

    /**
     * @param $dest_id
     * @return bool|mixed
     * 获取目的地地址
     */
    public function getDestAddress($dest_id){
        $sql="SELECT * FROM ly_address WHERE `status`=99 AND first='Y' AND dest_id=".$dest_id;
        return $this->query($sql);
    }

    /**
     * @param $dest_id
     * @return bool|mixed
     * 获取目的地门票信息
     */
    public function getDestTicketInfo($dest_id){
        $sql="SELECT * FROM ly_ticket WHERE `status`=99 AND dest_id=".$dest_id;
        return $this->query($sql);
    }

    /**
     * @param $dest_id
     * @return bool|mixed
     * 获取目的地联系方式
     */
    public function getDestContract($dest_id){
        $sql="SELECT * FROM  ly_contact WHERE `status`=99 AND dest_id=".$dest_id;
        return $this->query($sql);
    }

    /**
     * @param $dest_id
     * @param string $type
     * @return bool|mixed
     * 获取目的地建议游玩时间
     */
    public function getDestTime($dest_id,$type='SALE_TIME'){
        $sql="SELECT * FROM ly_time WHERE `status`=99 AND `object_type`='".$type."'  AND dest_id=".$dest_id." ORDER BY `first` DESC ";
        return $this->query($sql,'All');
    }

    /**
     * @param $dest_id
     * @return bool|mixed
     * 获取目的地景点简述
     */
    public function getDestScenerySummary($dest_id){
        $sql="SELECT text FROM ly_data WHERE `status`=99 AND title='景点概述' AND dest_id=".$dest_id;
        $result= $this->query($sql);
        if($result){
            return $result['text'];
        }
    }


    /**
     *
     */
    public function getUrlPinyin($district_id){
        $sql = "SELECT url_pin_yin FROM ly_district WHERE district_id=".$district_id;
        $result= $this->query($sql);
        if($result){
            return $result['url_pin_yin'];
        }else{
            return '';
        }
    }


    public function getAllPoi($dest_id){
        $sql = "SELECT `dest_id`,`pinyin`,`dest_name` FROM `ly_destination` WHERE `parent_id` = {$dest_id} AND `dest_type` = 'VIEWSPOT' AND`showed` ='Y'AND `cancel_flag` ='Y'";
        $result= $this->query($sql, 'All');
        if($result){
            return $result;
        }
    }

}