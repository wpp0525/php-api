<?php
namespace Semsearch;

class SanLiuLingCommonService extends CommonService{

    public $accessToken;
    public $authHeaderArr;
    public $auth;

    public function setAuthHeader($authHeader){
        $this->auth = $authHeader;

        if (isset($authHeader->username) && isset($authHeader->password) && isset($authHeader->apiSecret)) {
            $userReq = array();

            $userReq['format'] = 'json';
            $userReq['username'] = $authHeader->username;
            $userReq['passwd'] = $this->getPassword($authHeader);

            if(!isset($this->authHeader) && isset($authHeader)){

                $authHeaderNew['serveToken'] = "serveToken:".$this->getMillisecond();
                $authHeaderNew['apiKey'] = "apiKey:".$authHeader->apiKey;
                $authHeaderNew['content_type'] = "Content-Type: application/x-www-form-urlencoded";
                $this->authHeaderArr = array_values($authHeaderNew);
                $this->authHeader = (object)$this->authHeaderArr;
            }

            $tmpServiceName = $this->serviceName;
            $this->serviceName = "account";
            $rs = $this->execute('clientLogin', $userReq);
            $this->serviceName = $tmpServiceName;
            $this->accessToken = isset($rs->accessToken)?$rs->accessToken:'';
            if($this->accessToken){
                $this->addAuthHeader($this->accessToken);
            }
        }

    }
    private function getPassword($params){
        $passwdOri= md5($params->password);
        $m = new Xcrypt(substr($params->apiSecret, 0, 16), 'cbc', substr($params->apiSecret, 16, 16));
        $passwd = $m->encrypt($passwdOri, 'hex');
        return $passwd;
    }
    private function getMillisecond(){
        list($t1, $t2) = explode(' ', microtime());
        $microtime =(floatval($t1)+floatval($t2))*1000000;
        $time = number_format($microtime,0,'','');
        return $time;
    }
    public function addAuthHeader($accessToken){
        $this->authHeaderArr[] = "accessToken:".$accessToken;
        $this->authHeader = (object)$this->authHeaderArr;
    }
    public function setJsonHeader($jsonHeader){
        $this->jsonHeader = $jsonHeader;
    }
    public function getJsonHeader(){
        $rs = parent::getJsonHeader();
        $rs->desc = in_array($rs->resultCode, array(10000, 10001)) ? 'success' : null;
        $rs->rquota = $rs->quotaRemain;
        return $rs;
    }

}
