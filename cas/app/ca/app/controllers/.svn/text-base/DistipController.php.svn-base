<?php

use Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Components\ApiClient;

class DistipController  extends ControllerBase
{
    /**
     * @var \Lvmama\Cas\Service\DistBaseIpService
     */
    private $dest_ip_srv;
    /**
     * @var \Lvmama\Cas\Service\DistBaseDataService
     */
    private $dist_base_srv;

    public function initialize() {
        parent::initialize();
        $this->dest_ip_srv = $this->di->get('cas')->get('dist_base_ip_service');
        $this->dist_base_srv = $this->di->get('cas')->get('dist_base_service');
    }

    /**
     * 根据IPv4地址返回行政区ID
     * @param ip ip地址
     * @param type 需要返回的最小行政区类型
     * @return string | json
     * @author donghongya & shenxiang
     */
    public function ipToDistAction(){
        $ip = $this->request->get('ip');
        $type = $this->request->get('type');
        if(!$ip) $this->_errorResponse(10001,'请传入ip');
        if(!UCommon::isIp($ip)) $this->_errorResponse(10002,'请传入正确的ip');
        $ip_num = ip2long($ip);
        $result = $this->dest_ip_srv->getOneByIpNum($ip_num);
        $district_id = 0;
        if (isset($result['district_id'])){
            $district_id = $result['district_id'];
        }
        if($type){
            $district = $this->dist_base_srv->getOneById($district_id);
            $district_id = $this->dest_ip_srv->getDistrictIdForType($district,$type);
        }
        $this->_successResponse(array('district_id' => $district_id));
    }
}
