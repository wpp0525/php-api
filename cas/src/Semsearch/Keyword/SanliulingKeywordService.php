<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Keyword;

use \Semsearch\SanLiuLingCommonService;
use Semsearch\SearchType;

class SanliulingKeywordService extends SanLiuLingCommonService implements KeywordServiceIface{
    public $pcStatus = array(

        'pause' => 1,
        'enable' => 2,
        'pending' => 3,
        'reject' => 4,
        'delete' => 5,
        'ineffective' => 6
    );
    public  $mobileStatus = array(
        'null' => 0,
        'pending' => 10,
        'pass' =>20,
        'reject' => 30,
        'ineffective' =>40
    );

    public function __construct() {
        parent::__construct ( SearchType::SANLIULING, '2.0/keyword' );
    }

    // ABSTRACT METHODS
    public function getWord ($getKeywordRequest){
        if ($this->accessToken) {
            $keywordInfoRs = new GetWordResponse();

            if ($getKeywordRequest->idType == '5') {//单元
                if (!empty($getKeywordRequest)) {
                    $keywordIds = array();
                    foreach ($getKeywordRequest->ids as $row) {
                        $getWordIdReq['groupId'] = $row;
                        $getWordIdReq['format'] = 'json';

                        $rs = $this->execute('getIdListByGroupId', $getWordIdReq);
                        isset($rs->keywordIdList) && $keywordIds=$rs->keywordIdList;
                        if (!empty($keywordIds)) {
                            $ids = array_unique($keywordIds);
                            $this->getKeywordInfoRs($ids,$keywordInfoRs);
                        }
                    }
                }
            } else {
                $ids = array_unique($getKeywordRequest->ids);
                $this->getKeywordInfoRs($ids,$keywordInfoRs);
            }
        }
        if(isset($keywordInfoRs->data)){
            return $keywordInfoRs;
        }
        return null;
    }
    private function getKeywordInfoRs($ids,&$keywordInfoRs){

        $idsLen = count($ids);
        $sliceLen = 1000;//1000个1批，接口限定
        $keywordRs = array();

        for ($i = 0; $i < $idsLen; $i = $i + $sliceLen) {
            $getKeywordInfoReq['format'] = 'json';
            $getKeywordInfoReq['idList'] = json_encode(array_slice($ids, $i, $sliceLen));

            $rs = $this->execute('getInfoByIdList', $getKeywordInfoReq);
            $keywordRs = array_merge($keywordRs, $rs->keywordList);
        }

        if (!empty($keywordRs)) {
            foreach ($keywordRs as $row) {
                $keywordInfo = array();
                if(isset($row->id)){
                    $keywordInfo['keywordId'] = $row->id;
                    $keywordInfo['adgroupId'] = isset($row->groupId)?$row->groupId:'0';
                    $keywordInfo['keyword'] = isset($row->word)?$row->word:'';
                    $keywordInfo['mobileDestinationUrl'] = isset($row->mobileDestinationUrl)?$row->mobileDestinationUrl:'';
                    $keywordInfo['pcDestinationUrl'] = isset($row->destinationUrl)?$row->destinationUrl:'';
                    $keywordInfo['price'] = isset($row->price)?$row->price:'0.00';
                    $keywordInfo['status'] = isset($row->status)?$this->pcStatus[$row->status]+$this->mobileStatus[$row->mobileStatus]:'0';

                    $keywordInfoRs->addData((object)$keywordInfo);
                }
            }
        }

    }

    public function addWord ($addKeywordRequest){
        return $this->execute ( 'addKeyword', $addKeywordRequest );
    }
    public function updateWord ($updateKeywordRequest){
        return $this->execute ( 'updateKeyword', $updateKeywordRequest );
    }
    public function deleteWord ($deleteKeywordRequest){
        return $this->execute ( 'deleteKeyword', $deleteKeywordRequest );
    }

}