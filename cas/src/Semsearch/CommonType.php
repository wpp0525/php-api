<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/10
 * Time: 16:02
 */

namespace Semsearch;


class CommonType{

    protected function mapping($params = null){
        if($params){
            //自动映射
            foreach ($params as $key => $value){
                $func = 'set' . ucfirst($key);
                if(method_exists($this, $func)){
                    $this->$func($value);
                }else{
                    throw new \Exception('method ' . $func .' dose not exist!');
                }
            }
        }
    }
}