<?php
namespace Semsearch;

use Phalcon\Exception;

define ('BAIDU_URL', 'https://api.baidu.com/json/sms/service');
define ('SHENMA_URL', 'https://e.sm.cn/api');
define ('SANLIULING_URL', 'https://api.e.360.cn');
define ('SOGOU_URL', 'http://api.agent.sogou.com:8080/sem/sms/v1');
define ('SOGOU_Header_URL', 'http://api.sogou.com/sem/common/v1');

class CommonService {
	public $serviceName;
	public $serviceurl;
	public $serviceHeaderUrl;

	public $authHeader;

	protected $searchType = SearchType::BAIDU;
	
	public $isJson = true;
	public $json_result;
	public $json_string;
	/**
	 * @return unknown
	 */
	public function getIsJson() {
		return $this->isJson;
	}
	
	/**
	 * @param unknown_type $isJson
	 */
	public function setIsJson($isJson) {
		$this->isJson = $isJson;
	}
	
	/**
	 * @return unknown
	 */
	public function getAuthHeader() {
		return $this->authHeader;
	}
	
	/**
	 * @return unknown
	 */
	public function getServiceurl() {
		return $this->serviceurl;
	}
	
	/**
	 * @param unknown_type $authHeader
	 */
    public function setAuthHeader($authHeader) {
		$this->authHeader = $authHeader;
    }
	
	/**
	 * @param unknown_type $serviceurl
	 */
	public function setServiceurl($serviceurl) {
		$this->serviceurl = $serviceurl;
	}

	public function setServiceName($serviceName){
		$this->serviceName = $serviceName;
	}

	//public $url;
	public function __construct($searchType, $serviceName) {

		switch ($searchType){
			case SearchType::BAIDU:
				$this->serviceurl = BAIDU_URL;
				break;
			case SearchType::SHENMA:
				$this->serviceurl = SHENMA_URL;
				break;
			case SearchType::SANLIULING:
				$this->serviceurl = SANLIULING_URL;
				break;
            case SearchType::SOGOU:
                $this->serviceurl = SOGOU_URL;
                $this->serviceHeaderUrl = SOGOU_Header_URL;
                break;
		}

		$this->searchType = $searchType;
		$this->serviceName = $serviceName;
	}
    protected function executeSoap($method,$request){
	    try{
//            throw new Exception("Value must be");
            $this->soapClient = new \SoapClient( $this->serviceurl . '/' . $this->serviceName  . '?wsdl', array ('trace' => TRUE, 'connection_timeout' => 30 ) );
            $sh_param = array ('agentusername' => '', 'agentpassword' => '', 'username' =>$this->authHeader->username, 'password' =>$this->authHeader->password, 'token' =>$this->authHeader->token );
            $headers = new \SoapHeader ( $this->serviceHeaderUrl, 'AuthHeader', $sh_param );
            $this->soapClient->__setSoapHeaders ( array ( $headers ) );

            $response = $this->soapClient->__soapCall($method, (array)$request, null, null, $outputHeaders);
            $this->json_result = new \StdClass();
            $this->json_result->header = isset($outputHeaders['ResHeader'])?$outputHeaders['ResHeader']:null;
            return $response;
        }catch(Exception $e){
	        echo "与搜狗服务器连接报错";
        }

	}
	protected function executeJson($method, $request, $isJson = true) {
		$ch = curl_init ();
		$url = $this->serviceurl . '/' . $this->serviceName . '/' . $method;

		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );

		$jsonEv = new JsonEnvelop ( );
		
		$jsonEv->setBody ( $request );
		$jsonEv->setHeader ( $this->authHeader );
		$data = json_encode ( $jsonEv );

		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data ); //$data是每个接口的json字符串
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); //不加会报证书问题
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); //不加会报证书问题
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json; charset=utf-8' ) );
		
		$this->json_string = curl_exec ( $ch );

		curl_close ($ch );
		if($isJson){
			$this->json_result = json_decode ( $this->json_string );
			if(isset($this->json_result->body)){
				return $this->json_result->body;
			}
		}
		return $this->json_string;
	}

    /**
     * @param $method
     * @param $request
     * @param bool $headerFlag curl是否返回header信息
     * @return array|mixed|null
     */
	protected function executeDefault($method, $request, $headerFlag = false){

		$ch = curl_init ();

		$url = $this->serviceurl . '/' . $this->serviceName . '/' . $method;

		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );

		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $request );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); //不加会报证书问题
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); //不加会报证书问题
		curl_setopt ( $ch, CURLOPT_HTTPHEADER,(array)$this->authHeader);

		if($headerFlag){//需返回headers信息
            curl_setopt($ch,CURLOPT_HEADER,1);
        }
		$this->json_string = curl_exec ( $ch );
        if($headerFlag){
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
                $list = explode("\r\n\r\n", $this->json_string);
                $len = count($list);
                $body = $list[$len-1];
                $header = $list[$len-2];
				$this->json_result = new JsonEnvelop();
				$this->json_result->header = $this->encodeHeader($header);
				$this->json_result->body = json_decode($body);
            }
        }else{
            $this->json_result = json_decode ( $this->json_string );
        }
		curl_close ($ch );

		if(isset($this->json_result->body)){
			return $this->json_result->body;
		}
		return null;
	}
    public function execute($method, $request) {
        switch ($this->searchType){
            case SearchType::BAIDU:
            case SearchType::SHENMA:
                return $this->executeJson ( $method, $request );
                break;
            case SearchType::SANLIULING:
                return $this->executeDefault($method, http_build_query((array)$request), true);
                break;
            case SearchType::SOGOU:
                return $this->executeSoap($method, $request);
                break;
            default:
                return false;
        }
    }
	public function downloadFile($method, $request, $filename){
		$content = $this->executeJson($method, $request, false);

		if(!$filename){//直接返回内容
			return $content;
		}else{//保存文件

		}
	}
    public function getJsonHeader() {
        if(isset($this->json_result->header)) {
            return $this->json_result->header;
        }
        return null;
    }
	public function getJsonEnv() {
		return $this->json_result;
	}
	public function getJsonStr() {
		return $this->json_string;
	}

	/**
	 * @param string $header
	 * @return object
	 */
	private function encodeHeader($header){
		$header = explode("\r\n", $header);
		unset($header[0]);
		$res = new \stdClass();
		foreach ($header as $h){
			list($k, $v) = explode(": ", $h);
			$res->$k = $v;
		}
		return $res;
	}
}
class JsonEnvelop {
	public $header;
	public $body;
	
	/**
	 * @return unknown
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * @return unknown
	 */
	public function getHeader() {
		return $this->header;
	}
	
	/**
	 * @param unknown_type $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}
	
	/**
	 * @param unknown_type $header
	 */
	public function setHeader($header) {
		$this->header = $header;
	}

}

?>