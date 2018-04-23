<?php
namespace Lvmama\Cas\Service\Ora;

use Lvmama\Cas\Service\DataServiceBase;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * oracle 订单数据服务类
 *
 * @author libiying
 *
 */
class MarkChannelDataService extends DataServiceBase {


    private $columns = array(
        "CHANNEL_ID",
        "CHANNEL_NAME",
        "CHANNEL_CODE",
        "to_char(CREATE_TIME,'yyyy-mm-dd hh24:mi:ss') as CREATE_TIME",
        "FATHER_ID",
        "VALID",
        "LAYER",
        "CHANNEL_COMMENT",
        "RANGE",
        "PROFIT_SHARING",
        "APPLICATION_TYPE",
        "to_char(UPDATE_TIME,'yyyy-mm-dd hh24:mi:ss') as UPDATE_TIME",
    );

    public function getMarkChannelList($where_condition, $select = null){
        $select = $select ? " SELECT " . $select : " SELECT " . implode(',', $this->columns);
        $from = " FROM LVMAMA_PET.MARK_CHANNEL ";
        $where = $this->initWhere($where_condition);

        $sql = $select . $from . $where;
        $result = $this->query($sql, 'All');

        return $result;
    }

    public function getListWithFather($where_condition, $select = null){
    	$select = $select ? " SELECT " . $select : "SELECT c.CHANNEL_ID,c.CHANNEL_CODE,c.FATHER_ID,mc.CHANNEL_NAME AS FATHER_NAME,c.CHANNEL_NAME";
        $from = " FROM LVMAMA_PET.MARK_CHANNEL c ";
        $where = $this->initWhere($where_condition);
		$join = " LEFT JOIN MARK_CHANNEL mc ON mc.CHANNEL_ID = c.FATHER_ID ";

        $sql = $select . $from . $join . $where;
        $result = $this->query($sql, 'All');

        return $result;
    }

}