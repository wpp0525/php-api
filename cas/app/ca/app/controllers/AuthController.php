<?php

use Phalcon\Mvc\Controller;

/**
 * Auth登陆
 * @author mac.zhao
 */
class AuthController extends ControllerBase {
	
	public function initialize() {
		parent::initialize();
		$this->userDataService = $this->di->get('cas')->get('user-data-service');
	}
	
	/**
	 * 登陆状态验证 get.login.status
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "lvsessionid=3e93a43a-d4b5-495a-9a64-aa9112b97e0b_11288068" http://ca.lvmama.com/auth/login-status/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param lvsessionid | 客户端sessionid
	 * 
	 */
	public function checkLoginStatusAction() {
        $uid = $this->userDataService->getUidBySession($this->lvsessionid);
        
        if(empty($uid)) {
            $this->_errorResponse('100001', '验证失败');
        }
        
	    $content = array(
    		'uid' => $uid,
	    );
        $this->_successResponse($content);
	}
} 