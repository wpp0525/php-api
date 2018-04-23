<?php

namespace Lvmama\Cas\Component;

/**
 * Oracle 数据操作提供类
 * 
 * 
 * @author libiying
 *
 */
class OracleAdapter extends \Phalcon\Db\Adapter\Pdo\Oracle{

    public function __construct(array $descriptor = null){

        //如果配置了rac集群
        if($descriptor && isset($descriptor['racs'])){
            foreach ($descriptor['racs'] as $rac){
                try{
                    parent::__construct($rac);
                    return;
                }catch (\PDOException $ee){

                }
            }
            throw new \Exception("Can't connect to Oracle!");
        }else{
            parent::__construct($descriptor);
        }
    }

    public function bindParams($sqlStatement, $params){
        parent::bindParams($sqlStatement, $params);
    }

}