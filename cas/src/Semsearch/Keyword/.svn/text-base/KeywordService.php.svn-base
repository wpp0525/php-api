<?php
namespace Semsearch\Keyword;


use Semsearch\SearchType;


class KeywordService {

    /**
     * @var KeywordServiceIface
     */
    private $instance = null;

    private $searchType = null;

    public function __construct($searchType = SearchType::BAIDU) {

        $this->searchType = $searchType;

        switch ($searchType){
            case SearchType::BAIDU:
                $this->instance = new BaiduKeywordService();
                break;
            case SearchType::SHENMA:
                $this->instance = new ShenmaKeywordService();
                break;
            case SearchType::SANLIULING:
                $this->instance = new SanliulingKeywordService();
                break;
            case SearchType::SOGOU:
                $this->instance = new SogouKeywordService();
                break;
        }

    }

    public function __call($name, $arguments){
        // TODO: Implement __call() method.
        if(method_exists($this->instance, $name)){
            return call_user_func_array(array($this->instance, $name), $arguments);
        }
        throw new \Exception("Call Wrong Function!!");
    }

    public function updateWord ($updateWordRequest){
        return $this->instance->updateWord ( $updateWordRequest );
    }
    public function addWord ($addWordRequest){
        return $this->instance->addWord ( $addWordRequest );
    }
    public function deleteWord ($deleteWordRequest){
        return $this->instance->deleteWord ( $deleteWordRequest );
    }
    public function getWord ($getWordRequest){
        return $this->instance->getWord ( $getWordRequest );
    }

    public function getJsonHeader() {
        return $this->instance->getJsonHeader();
    }

    public function setAuthHeader($authHeader){
        $this->instance->setAuthHeader($authHeader);
    }

}

