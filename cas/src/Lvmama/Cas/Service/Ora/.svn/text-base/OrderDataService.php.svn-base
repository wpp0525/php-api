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
class OrderDataService extends DataServiceBase {


    private $columns = array(
        "ORDER_ID",
        "ORI_ORDER_ID",
        "DISTRIBUTOR_ID",
        "DISTRIBUTOR_CODE",
        "ORDER_STATUS",
        "PAYMENT_STATUS",
        "RESOURCE_STATUS",
        "INFO_STATUS",
        "to_char(LAST_CANCEL_TIME,'yyyy-mm-dd hh24:mi:ss') as LAST_CANCEL_TIME",
        "to_char(WAIT_PAYMENT_TIME,'yyyy-mm-dd hh24:mi:ss') as WAIT_PAYMENT_TIME",
        "CANCEL_CODE",
        "REASON",
        "CURRENCY_CODE",
        "OUGHT_AMOUNT",
        "ACTUAL_AMOUNT",
        "USER_ID",
        "USER_NO",
        "GUARANTEE",
        "PAYMENT_TARGET",
        "to_char(CREATE_TIME,'yyyy-mm-dd hh24:mi:ss') as CREATE_TIME",
        "PAYMENT_TYPE",
        "VIEW_ORDER_STATUS",
        "INVOICE_STATUS",
        "REMARK",
        "FILIALE_NAME",
        "to_char(PAYMENT_TIME,'yyyy-mm-dd hh24:mi:ss') as PAYMENT_TIME",
        "BACK_USER_ID",
        "to_char(APPROVE_TIME,'yyyy-mm-dd hh24:mi:ss') as APPROVE_TIME",
        "to_char(VISIT_TIME,'yyyy-mm-dd hh24:mi:ss') as VISIT_TIME",
        "to_char(CANCEL_TIME,'yyyy-mm-dd hh24:mi:ss') as CANCEL_TIME",
        "BOOK_LIMIT_TYPE",
        "CLIENT_IP_ADDRESS",
        "CERT_CONFIRM_STATUS",
        "CANCEL_CERT_CONFIRM_STATUS",
        "ORDER_MEMO",
        "REFUNDED_AMOUNT",
        "PROCESS_KEY",
        "DISTRIBUTION_CHANNEL",
        "DEPOSITS_AMOUNT",
        "NOTIFY_TYPE",
        "REBATE_AMOUNT",
        "REBATE_FLAG",
        "BONUS_AMOUNT",
        "NEED_INVOICE",
        "PROM_PAYMENT_CHANNEL",
        "CATEGORY_ID",
        "MOBILE_MORE_REBATE",
        "MANAGER_ID",
        "BU_CODE",
        "VALID_STATUS",
        "REMIND_SMS_SEND_STATUS",
        "ATTRIBUTION_ID",
        "CANCEL_FAIL_COUNT",
        "ANONYMITY_BOOK_FLAG",
        "INVOKE_INTERFACE_PF_STATUS",
        "SUPPLIER_API_FLAG",
        "COMPANY_TYPE",
        "to_char(RESOURCE_AMPLE_TIME,'yyyy-mm-dd hh24:mi:ss') as RESOURCE_AMPLE_TIME",
        "to_char(INFO_PASS_TIME,'yyyy-mm-dd hh24:mi:ss') as INFO_PASS_TIME",
        "LINE_ROUTE_ID",
        "START_DISTRICT_ID",
        "MOBILE_EQUIPMENT_NO",
        "IS_TEST_ORDER",
        "DISTRIBUTOR_NAME",
        "MANAGER_ID_PERM",
        "SUB_CATEGORY_ID",
        "to_char(ORDER_UPDATE_TIME,'yyyy-mm-dd hh24:mi:ss') as ORDER_UPDATE_TIME",
        "PAY_PROC_TRIGGERED",
        "SEND_CONTRACT_FLAG",
        "SMS_LVMAMA_FLAG",
        "CANCEL_STRATEGY",
        "TAG",
        "TRAVELLER_LOCK_FLAG",
        "TRAVELLER_DELAY_FLAG",
        "STAMPS_AMOUNT",
        "ORDER_SUBTYPE",
        "PERFORM_STATUS",
        "PRE_REFUND_STATUS",
        "to_char(END_TIME,'yyyy-mm-dd hh24:mi:ss') as END_TIME",
        "APP_VERSION",
        "PAY_PROMOTION_AMOUNT",
        "to_char(TICKET_LAST_CONFIRM_TIME,'yyyy-mm-dd hh24:mi:ss') as TICKET_LAST_CONFIRM_TIME",
        "ORDER_CREATING_MANNER",
        "DISTRIBUTION_CPSID",
        "UPDATE_TIME",
        "TRAV_DELAY_FLAG",
        "TRAV_DELAY_STATUS",
        "to_char(TRAV_DELAY_WAIT_TIME,'yyyy-mm-dd hh24:mi:ss') as TRAV_DELAY_WAIT_TIME",
        "AD_TRAV_REMIND_STATUS",
        "AD_TRAV_REMIND_STATUS",
    );


    public function getOrderWithLosc($where_condition, $select = null, $group = null){

        $select = $select ? " SELECT " . $select : " SELECT " . implode(',', $this->columns);
        $from = " FROM LVMAMA_VER.ORD_ORDER ";
        $join = " INNER JOIN LVMAMA_VER.ORD_ORDER_LOSC ON ORD_ORDER.ORDER_ID = ORD_ORDER_LOSC.ORDER_ID ";
        $where = $this->initWhere($where_condition);
        $group = $group ? " GROUP BY " . $group : "";

        $sql =  $select . $from .  $join .  $where . $group;
        $result = $this->query($sql, 'All');

        return $result;
    }

    public function getOrderList($where_condition, $select = null){
        $select = $select ? " SELECT " . $select : " SELECT " . implode(',', $this->columns);
        $from = " FROM LVMAMA_VER.ORD_ORDER ";
        $where = $this->initWhere($where_condition);

        $sql = $select . $from . $where;
        $result = $this->query($sql, 'All');

        return $result;
    }

}