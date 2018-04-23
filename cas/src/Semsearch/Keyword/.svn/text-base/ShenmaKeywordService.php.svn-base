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

class ShenmaKeywordService extends CommonService implements KeywordServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SHENMA, 'keyword' );
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
                $req['adgroupIds'] = array_slice($ids, $i, $sliceLen);
                $rs = $this->execute('getKeywordByAdgroupId', (object)$req);
                if(isset($rs->groupKeywords)){
                    foreach($rs->groupKeywords as $row){
                        foreach($row->keywordTypes as $keywordRow){
                            $this->getKeywordInfoRs($keywordRow,$keywordInfoRs);
                        }
                    }
                }
            }

        }else{

            $ids = $getKeywordRequest->ids;
            $idsLen = count($ids);
            $sliceLen = 5000;//5000个1批,接口限定

            for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
                $req['keywordIds'] = array_slice($ids, $i, $sliceLen);
                $rs = $this->execute('getKeywordByKeywordId', (object)$req);
                if(isset($rs->keywordTypes)){
                    foreach($rs->keywordTypes as $keywordRow){
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
        if(isset($keywordRow->keywordId)){
            $keywordInfo['keywordId'] = $keywordRow->keywordId;
            $keywordInfo['adgroupId'] = isset($keywordRow->adgroupId)?$keywordRow->adgroupId:'0';
            $keywordInfo['keyword'] = isset($keywordRow->keyword)?$keywordRow->keyword:'';
            $keywordInfo['mobileDestinationUrl'] = isset($keywordRow->destinationUrl)?$keywordRow->destinationUrl:'';
            $keywordInfo['status'] = isset($keywordRow->status)?$keywordRow->status:'0';
            $keywordInfo['price'] = isset($keywordRow->price)?$keywordRow->price:'0.00';

            $keywordInfoRs->addData((object)$keywordInfo);
        }

    }
    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="执行成功"?"success":"";
        }
        return $rs;
    }
}