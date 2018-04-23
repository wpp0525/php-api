<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Adgroup;

use \Semsearch\CommonService;
use Semsearch\SearchType;

class ShenmaAdgroupService extends CommonService implements AdgroupServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SHENMA, 'adgroup' );
    }

    // ABSTRACT METHODS
    public function getAdgroup ($getAdgroupRequest){
        $adgroupInfoRs = new GetAdgroupResponse();
        if($getAdgroupRequest->idType==3){

            $ids = $getAdgroupRequest->idList;
            $idsLen = count($ids);
            $sliceLen = 10;//10个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $req['campaignIds'] = array_slice($ids, $i, $sliceLen);
                $rs = $this->execute('getAdgroupByCampaignId', (object)$req);
                if(isset($rs->campaignAdgroups)){
                    foreach($rs->campaignAdgroups as $row){
                        foreach($row->adgroupTypes as $adgroupRow){
                            $this->getAdgroupInfoRs($adgroupRow,$adgroupInfoRs);
                        }

                    }
                }
            }

        }else{

            $ids = $getAdgroupRequest->idList;
            $idsLen = count($ids);
            $sliceLen = 1000;//1000个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $req['adgroupIds'] = array_slice($ids, $i, $sliceLen);
                $rs = $this->execute('getAdgroupByAdgroupId', (object)$req);
                if(isset($rs->adgroupTypes)){
                    foreach($rs->adgroupTypes as $adgroupRow){
                        $this->getAdgroupInfoRs($adgroupRow,$adgroupInfoRs);

                    }
                }
            }

        }
        if(isset($adgroupInfoRs->data)){
            return $adgroupInfoRs;
        }
        return null;
    }
    private function getAdgroupInfoRs($adgroupRow,&$adgroupInfoRs){
        $adgroupInfo = array();
        if(isset($adgroupRow->adgroupId)){
            $adgroupInfo['adgroupId'] = $adgroupRow->adgroupId;
            $adgroupInfo['campaignId'] = isset($adgroupRow->campaignId)?$adgroupRow->campaignId:'0';
            $adgroupInfo['adgroupName'] = isset($adgroupRow->adgroupName)?$adgroupRow->adgroupName:'';
            $adgroupInfo['status'] = isset($adgroupRow->status)?$adgroupRow->status:'0';

            $adgroupInfoRs->addData((object)$adgroupInfo);
        }

    }
    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="执行成功"?"success":"";
        }
        return $rs;
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