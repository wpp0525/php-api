<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Keyword;

use \Semsearch\CommonService;
use Semsearch\SearchType;

class SogouKeywordService extends CommonService implements KeywordServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SOGOU, 'CpcService' );
    }

    // ABSTRACT METHODS

    public function addWord ($addKeywordRequest){
        return $this->execute ( 'addKeyword', $addKeywordRequest );
    }
    public function updateWord ($updateKeywordRequest){
        return $this->execute ( 'updateKeyword', $updateKeywordRequest );
    }
    public function deleteWord ($deleteKeywordRequest){
        return $this->execute ( 'deleteKeyword', $deleteKeywordRequest );
    }
    public function getWord ($getKeywordRequest){
        $keywordInfoRs = new GetWordResponse();
        if($getKeywordRequest->idType==5){//根据单元id请求

            $ids = $getKeywordRequest->ids;
            $idsLen = count($ids);
            $sliceLen = 10;//10个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $idSlice = array_slice($ids, $i, $sliceLen);
                $req = array(
                    'getCpcByCpcGrpIdRequest' => array(
                        'cpcGrpIds' => $idSlice
                    )
                );
                $rs = $this->execute('getCpcByCpcGrpId', $req);
                if(isset($rs->cpcGrpCpcs)){
                    $infos = is_array($rs->cpcGrpCpcs)?$rs->cpcGrpCpcs:array($rs->cpcGrpCpcs);
                    foreach($infos as $row){
                        if(isset($row->cpcTypes)){
                            $cpcTypes = is_array($row->cpcTypes)?$row->cpcTypes:array($row->cpcTypes);
                            foreach($cpcTypes as $keywordRow){

                                $this->getKeywordInfoRs($keywordRow,$keywordInfoRs);
                            }
                        }
                    }
                }
            }

        }else{

            $ids = $getKeywordRequest->ids;
            $idsLen = count($ids);
            $sliceLen = 5000;//5000个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $idSlice = array_slice($ids, $i, $sliceLen);
                $req = array(
                    'getCpcByCpcIdRequest' => array(
                        'cpcIds' => $idSlice
                    )
                );
                $rs = $this->execute('getCpcByCpcId', $req);
                if(isset($rs->cpcTypes)){
                    $keywords = is_array($rs->cpcTypes)?$rs->cpcTypes:array($rs->cpcTypes);
                    foreach($keywords as $keywordRow){
                        $this->getKeywordInfoRs($keywordRow,$keywordInfoRs);

                    }
                }
            }
        }
        if(isset($keywordInfoRs->data)){
            return $keywordInfoRs;
        }
        return null;
    }

    private function getKeywordInfoRs($keywordRow,&$keywordInfoRs){
        $keywordInfo = array();
        if(isset($keywordRow->cpcId)){
            $keywordInfo['keywordId'] = $keywordRow->cpcId;
            $keywordInfo['adgroupId'] = isset($keywordRow->cpcGrpId)?$keywordRow->cpcGrpId:'0';
            $keywordInfo['keyword'] = isset($keywordRow->cpc)?$keywordRow->cpc:'';
            $keywordInfo['pcDestinationUrl'] = isset($keywordRow->visitUrl)?$keywordRow->visitUrl:'';
            $keywordInfo['status'] = isset($keywordRow->status)?$keywordRow->status:'0';
            $keywordInfo['price'] = isset($keywordRow->price)?$keywordRow->price:'0.00';

            $keywordInfoRs->addData((object)$keywordInfo);
        }

    }
    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="success"?"success":"";
        }
        return $rs;
    }
}