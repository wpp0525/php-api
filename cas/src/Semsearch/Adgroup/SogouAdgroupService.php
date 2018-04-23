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

class SogouAdgroupService extends CommonService implements AdgroupServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SOGOU, 'CpcGrpService' );
    }

    // ABSTRACT METHODS
    public function getAdgroup ($getAdgroupRequest){
        $adgroupInfoRs = new GetAdgroupResponse();
        if($getAdgroupRequest->idType==3){

            $ids = $getAdgroupRequest->idList;
            $idsLen = count($ids);
            $sliceLen = 10;//10个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $idSlice = array_slice($ids, $i, $sliceLen);
                $req = array(
                    'getCpcGrpByCpcPlanIdRequest' => array(
                        'cpcPlanIds' => $idSlice
                    )
                );
                $rs = $this->execute('getCpcGrpByCpcPlanId',$req);
                if(isset($rs->cpcPlanGrps)){
                    $infos = is_array($rs->cpcPlanGrps)?$rs->cpcPlanGrps:array($rs->cpcPlanGrps);
                    foreach($infos as $row){
                        foreach($row->cpcGrpTypes as $adgroupRow){
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
                $idSlice = array_slice($ids, $i, $sliceLen);
                $req = array(
                    'getCpcGrpByCpcGrpIdRequest' => array(
                        'cpcGrpIds' => $idSlice
                    )
                );
                $rs = $this->execute('getCpcGrpByCpcGrpId', $req);

                if(isset($rs->cpcGrpTypes)){
                    $groups = is_array($rs->cpcGrpTypes)?$rs->cpcGrpTypes:array($rs->cpcGrpTypes);
                    foreach($groups as $adgroupRow){
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
        if(isset($adgroupRow->cpcGrpId)){
            $adgroupInfo['adgroupId'] = $adgroupRow->cpcGrpId;
            $adgroupInfo['campaignId'] = isset($adgroupRow->cpcPlanId)?$adgroupRow->cpcPlanId:'0';
            $adgroupInfo['adgroupName'] = isset($adgroupRow->cpcGrpName)?$adgroupRow->cpcGrpName:'';
            $adgroupInfo['status'] = isset($adgroupRow->status)?$adgroupRow->status:'0';

            $adgroupInfoRs->addData((object)$adgroupInfo);
        }

    }
    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="success"?"success":"";
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