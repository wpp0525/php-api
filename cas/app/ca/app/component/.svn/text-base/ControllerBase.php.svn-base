<?php
use Lvmama\Common\Utils\Filelogger;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

/**
 * CAS API 控制器基类
 *
 * @author mac.zhao
 *
 */
class ControllerBase extends Controller
{
    private $_api_params = null;
    protected $beanstalk;
    protected $redis_svc;
    protected $redisConfig;
    protected static $justReturn = false;
    protected $slow_request_time = 10000;
    protected $baseUri           = 'http://ca.lvmama.com';
    protected function initialize()
    {
        $this->_start = microtime(true);
        //$this->_current    = '';
        $this->beanstalk   = $this->di->get('cas')->getBeanstalk();
        $this->redis_svc   = $this->di->get('cas')->get('redis_data_service');
        $this->redisConfig = $this->di->get('redisConfig');
        $this->response    = new Response();
        $params            = $this->dispatcher->getParams();
        if (isset($params['code']) || isset($params['error'])) {
            //如果出现错误信息,打印出来,以便调试
            var_dump($params);
        }
        //$this->_step = microtime(true);
        //         $this->api = $params['api'];
        return $this->_setParams();
//         preg_match('/(iOS|Android)/', Misc::getallheaders()['User-Agent'], $matches);
        //         preg_match('/(iOS|Android)/', 'Android', $matches);
        //         if(!empty($matches)) {
        //             $token = $this->config->app->toArray()[strtolower($matches[1])]['token_key'];
        //             $secure = $this->config->app->toArray()[strtolower($matches[1])]['secure_key'];
        //         }
        //         else {
        //             $this->_errorResponse('100000', '非法请求');
        //         }

//         if(false && $params['sign'] != Misc::sign(self::PATTERN_TOKEN_SIGN, $params, $secure)) {
        //             $this->_errorResponse('100001', '非法请求');
        //         }
    }

    protected function _runtime()
    {
        $cost = round((microtime(true) - $this->_start) * 1000, 4) . "ms";

        return $cost;
    }

    /**
     * JSON 输出
     * @param array $data
     * @return Ambigous <\Phalcon\Http\Response, \Phalcon\HTTP\ResponseInterface>
     */
    protected function jsonResponse($data)
    {

        /**********  将比较慢的查询写入日志  lixiumeng*******/
        $runtime = $this->_runtime();

        if ($runtime > $this->slow_request_time) {
            $msg = [
                'REQUEST_URI'   => $_SERVER['REQUEST_URI'],
                'GET_PARAMS'    => $_GET,
                'POST_PARAMS'   => $_POST,
                'RESPONSE_DATA' => $data,
                'RUNTIME'       => $runtime,
                'REQUEST_ADDR'  => $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'],
                'REQUEST_TIME'  => date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']),
            ];

            $this->_writeSlowRequestLog(json_encode($msg, JSON_UNESCAPED_UNICODE), 'warning');
        }

        /********将比较慢的查询写入日志  lixiumeng********/

        $this->view->disable();
        if (self::$justReturn) {
            return $this->response->setJsonContent($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $this->response->setStatusCode(200, 'OK');
        $this->response->setContentType('application/json', 'utf-8');
        $this->response->setJsonContent($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->response->send();
        die();
    }

    /**
     * 返回成功结果
     *
     * @param array $data
     * @return Ambigous
     */
    protected function successResponse($data)
    {
        $this->jsonResponse(array_merge(array('api' => $this->getApiParams('api'), 'error' => 0), $data));
        return true;
    }

    /**
     * 返回错误
     *
     * @param integer $code
     * @param string $error
     * @return Ambigous
     */
    protected function errorResponse($code, $error)
    {
        $this->jsonResponse(array('api' => $this->getApiParams('api'), 'error' => $code, 'error_description' => $error));
        return false;
    }

    /**
     * 动态获取请求参数
     *
     * @todo 通过rule变量匹配参数规则，不符合规则报错
     */
    protected function _setParams()
    {
        $params = $this->parameter->toArray()[strtolower($this->router->getControllerName())][$this->router->getActionName()];
        //使用thrift时不支持router
        if (empty($this->router->getControllerName())) {
            $_url                              = isset($_GET['_url']) ? $_GET['_url'] : (isset($_POST['_url']) ? $_POST['_url'] : (isset($_REQUEST['_url']) ? $_REQUEST['_url'] : ""));
            $_url                              = explode("::", trim($_url, "::"));
            $controllerName                    = explode("\\", trim($_url[0], "\\"));
            $controllerName                    = $controllerName[count($controllerName) - 1];
            !empty($controllerName) && $params = $this->parameter->toArray()[strtolower(str_replace("Implement", "", $controllerName))][str_replace("Action", "", $_url[1])];
            unset($_GET['_url'], $_POST['_url'], $_REQUEST['_url']);
            $this->_api_params = null; //因常驻服务故需重新初始化
        }
        if (empty($params)) {
            return true;
        }

        foreach ($params as $key => $param) {
            $this->getApiParams($param['input']);
            //参数类型检查
            if ($param['required'] && !array_key_exists($param['input'], $this->_api_params)) {
                return $this->_errorResponse('100002', '参数' . $param['input'] . '必填');
            }
            if ($param['required'] && $param['rule'] && !preg_match('#' . $param['rule'] . '#i', $this->_api_params[$param['input']])) {
                return $this->_errorResponse('100002', '参数' . $param['input'] . '类型不匹配');
            }
            $this->$key = isset($this->_api_params[$param['input']]) ? $this->_api_params[$param['input']] : $param['default'];
        }
        return true;
    }
    /**
     * 获取Api请求参数
     *
     * @return array
     */
    protected function getApiParams($key = null, $defaultValue = null)
    {
        if (is_null($this->_api_params)) {
            $this->_api_params = array_merge($_GET, $_POST, $this->dispatcher->getParams());
            unset($this->_api_params['_url']);
        }
        return is_null($key) ? $this->_api_params : (array_key_exists($key, $this->_api_params) ? $this->_api_params[$key] : (is_null($defaultValue) ? null : $defaultValue));
    }

    /**
     * 获取当前Token信息
     *
     * @return \ApiToken
     */
    protected function getTokenInfo()
    {
        return unserialize($this->dispatcher->getParam('_token_validated'));
    }

    /**
     * TODO 迁移
     * @param int $len
     * @return Ambigous <string, number>
     */
    protected static function createSecode($len = 6)
    {
        $secode = '';
        for ($i = 0; $i < 6; $i++) {
            $secode .= mt_rand(0, 9);
        }
        return $secode;
    }

    /**
     * 返回成功结果
     *
     * @param $data
     * @return Ambigous
     */
    protected function _successResponse($data = 'SUCCESS')
    {
        $this->jsonResponse(array('error' => 0, 'result' => $data));
        return true;
    }

    /**
     * 返回错误
     *
     * @param integer $code
     * @param string $error
     * @return Ambigous
     */
    protected function _errorResponse($code, $error)
    {
        $this->jsonResponse(array('error' => $code, 'error_description' => $error));
        return false;
    }

    /**
     * api url请求 josn格式转数组
     * @param string $url
     * @return array $data
     */
    public function api($url)
    {
        $string = file_get_contents($url);
        //过滤非标准json格式字符
        $aBP = strpos($string, '['); //数组符号第一个位置
        $oBP = strpos($string, '{'); //对象符号第一个位置
        //如果都不存在有这两个符号，表示非json数据，直接返回原始数据
        if ($aBP === false && $oBP === false) {
            $data = $string;
        } else {
            $aEP = strrpos($string, ']'); //数组符号最后一个位置
            $oEP = strrpos($string, '}'); //对象符号最后一个位置
            //否则,如果只存在{，那么只返回对象部分数据
            if ($aBP === false) {
                $jsonData = substr($string, $oBP, ($oEP - $oBP + 1));
            } elseif ($oBP === false) {
                //如果只存在[,那么只返回数组部分数据
                $jsonData = substr($string, $aBP, ($aEP - $aBP + 1));
            } else {
                //[和{都存在，那么比较位置大小，取值最小的
                $bP       = min($aBP, $oBP);
                $eP       = ($bP == $aBP) ? $aEP : $oEP;
                $jsonData = substr($string, $bP, ($eP - $bP + 1));
            }
            $data = json_decode($jsonData, true);
            //超时或者无效接口，直接返回错误信息
            if (isset($data['error']) && $data['error'] && isset($data['status']) && !$data['status']) {
                return array('error' => 'api timeout error');
            }

            //判断是否json数据,非json数据，返回获取到的字符串
            if ($data === null) {
                $data = $string;
            }
        }
        return $data;
    }

    /**
     * 写慢请求日志
     * @author lixiumeng
     * @datetime 2017-08-31T17:42:38+0800
     * @param    [type]                   $logData [description]
     * @return   [type]                            [description]
     */
    private function _writeSlowRequestLog($message, $log_level)
    {
        Filelogger::getInstance()->addLog($message, $log_level);

    }
}
