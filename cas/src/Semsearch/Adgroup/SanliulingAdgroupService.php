<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Adgroup;

use \Semsearch\SanLiuLingCommonService;
use Semsearch\SearchType;

class SanliulingAdgroupService extends SanLiuLingCommonService implements AdgroupServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SANLIULING, '2.0/group' );

    }

    // ABSTRACT METHODS
    public function getAdgroup ($getAdgroupRequest){

        if ($this->accessToken) {
            $adgroupInfoRs = new GetAdgroupResponse();

            if ($getAdgroupRequest->idType == '3') {//计划
                if (!empty($getAdgroupRequest)) {
                    $adgroupIds = array();
                    foreach ($getAdgroupRequest->idList as $row) {
                        $getAdgroupIdReq['campaignId'] = $row;
                        $getAdgroupIdReq['format'] = 'json';

                        $rs = $this->execute('getIdListByCampaignId', $getAdgroupIdReq);
                        isset($rs->groupIdList) && $adgroupIds=$rs->groupIdList;
                        if (!empty($adgroupIds)) {
                            $ids = array_unique($adgroupIds);
                            $this->getAdgroupInfoRs($ids,$adgroupInfoRs);
                        }
                    }
                    //设置
                    $headerRs['desc'] ='';
                    if(isset($adgroupInfoRs->data)){
                        $headerRs['desc'] = 'success';
                        $this->setJsonHeader((object)$headerRs);
                    }
                }
            } else {
                $ids = array_unique($getAdgroupRequest->idList);
                $this->getAdgroupInfoRs($ids,$adgroupInfoRs);

                $headerRs['desc'] ='';
                if(isset($adgroupInfoRs->data)){
                    $headerRs['desc'] = 'success';
                    $this->setJsonHeader((object)$headerRs);
                }
            }
        }
        if(isset($adgroupInfoRs->data)){
            return $adgroupInfoRs;
        }
        return null;
    }
    private function getAdgroupInfoRs($ids,&$adgroupInfoRs){

        $idsLen = count($ids);
        $sliceLen = 1000;//1000个1批，接口限定
        $adgroupRs = array();

        for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
            $getAdgroupInfoReq['format'] = 'json';
            $getAdgroupInfoReq['idList'] = json_encode(array_slice($ids, $i, $sliceLen));

            $rs = $this->execute('getInfoByIdList', $getAdgroupInfoReq);
            $adgroupRs = array_merge($adgroupRs, $rs->groupList);
        }

        if (!empty($adgroupRs)) {

            foreach ($adgroupRs as $row) {
                $adgroupInfo = array();
                if(isset($row->id)){
                    $adgroupInfo['adgroupId'] = $row->id;
                    $adgroupInfo['campaignId'] = isset($row->campaignId)?$row->campaignId:'0';
                    $adgroupInfo['adgroupName'] = isset($row->name)?$row->name:'';
                    $adgroupInfo['status'] = isset($row->status) && $row->status=='enable'?1:0;

                    $adgroupInfoRs->addData((object)$adgroupInfo);
                }

            }
        }

    }

    public function addAdgroup ($addAdgroupRequest){
        return $this->execute ( 'addAdgroup', $addAdgroupRequest );
    }
    public function updateAdgroup ($updateAdgroupRequest){
        return $this->execute ( 'updateAdgroup', $updateAdgroupRequest );
    }
    public function deleteAdgroup ($deleteAdgroupRequest){
        return $this->execute ( 'deleteAdgroup', $deleteAdgroupRequest );
    }


}