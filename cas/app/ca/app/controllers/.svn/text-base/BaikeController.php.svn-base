<?php
use Lvmama\Common\Utils\UCommon;
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2016/7/20
 * Time: 11:05
 */
class BaikeController extends ControllerBase
{
    public function initialize(){
        return parent::initialize();
    }
    public function indexAction(){
		$soap = $this->di->get('soapAliyun');
        $rs = $soap->query('select content from productContent WHERE productId = 181543','');
        //$rs = json_decode($soap->index());
        var_dump(json_decode($rs,true));
	}
}