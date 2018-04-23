<?php
/**
 * Created by PhpStorm.
 * User: libiying
 * Date: 2017/3/9
 * Time: 16:41
 */

namespace Semsearch\Keyword;

class UpdateWordRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //UpdateWordRequest Attributes
    public $keywordTypes;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setKeywordTypes($akeywordTypes) {
        $this->keywordTypes = $akeywordTypes;
    }

    public function addKeywordType($aKeywordType)
    {
        $wasAdded = false;
        $this->keywordTypes[] = $aKeywordType;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeKeywordType($aKeywordType)
    {
        $wasRemoved = false;
        unset($this->keywordTypes[$this->indexOfKeywordType($aKeywordType)]);
        $this->keywordTypes = array_values($this->keywordTypes);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getKeywordTypes()
    {
        $newKeywordTypes = $this->keywordTypes;
        return $newKeywordTypes;
    }

    public function numberOfKeywordTypes()
    {
        $number = count($this->keywordTypes);
        return $number;
    }

    public function indexOfKeywordType($aKeywordType)
    {
        $rawAnswer = array_search($aKeywordType,$this->keywordTypes);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}