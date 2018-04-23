<?php

use Phalcon\DiInterface;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

use Lvmama\Cas\Cas;
use Lvmama\Cas\Service\ApiDataService;
use Lvmama\Common\Utils\ArrayUtils;


/**
 * API流量监控器
 * 
 * @author mac.zhao
 *
 */
class ApiTrafficListener {
	
	/**
	 * @var ApiDataService
	 */
	private $ds;
	
	private $traffic_id = 0;
	
	private $is_api_request = false;
	
	private $debug = false;
	
	public function __construct($cas, $debug = false) {
		$this->ds = $cas->get('api-data-service');
		$this->debug = $debug;
	}
	
	/**
	 * 
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
		return true;
		if (!$dispatcher->wasForwarded()) {
			$request = $dispatcher->getDI()->get('request');
			$router = $dispatcher->getDI()->get('router');
			$client_ip = $request->getClientAddress();
			$api_params = $dispatcher->getParams();
			if ((count($api_params) == 4) 
					&& !ArrayUtils::keysExists(array('format', 'token', 'timestamp', 'sign'), $api_params)) {
				list($format, $token, $timestamp, $sign) = array_values($api_params);
				$api_params = array('format' => $format, 'token'=>$token, 'timestamp'=>$timestamp, 'sign'=>$sign);
			}

			$request_data = array_merge($_GET, $_POST, $api_params);
			unset($request_data['_url']);
			$error = array('code' => 0, 'error' => '');
			if (ArrayUtils::keysExists(array('api', 'token', 'timestamp', 'sign'), $request_data)) {
				$this->is_api_request = true;
				try {
					if (($checked = $this->ds->checkToken($request_data['token'], $request_data, null, $client_ip))
							&& ($token = \ApiToken::parse($checked))
							&& ($token instanceof \ApiToken)) {
						$dispatcher->setParam('_token_validated', serialize($token));
						$response_status = 0;
					} else {
						$response_status = -2;
						$error = array(
								'code' => 101010,
								'error' => 'Token未通过校验',
						);
					}
				} catch (\Exception $ex) {
					$response_status = intval(substr($ex->getCode(), -2));
					$error = array(
							'code' => $ex->getCode(),
							'error' => $ex->getMessage(),
					);
				}

				$traffic_data = array(
						'api_key' => $request_data['api'],
						'api_path' => $router->getRewriteUri(),
						'token_key' =>  $request_data['token'],
						'request_method' => $request->getMethod(),
						'request_params' => $this->debug ? json_encode($request_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null, // 只有在debug=true时才记录请求数据
						'response_status' => $response_status,
						'response_data' => $response_status ? json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
						'client_info' => isset($request_data['client_info']) ? $request_data['client_info'] : $client_ip
				);
				$this->traffic_id = $this->ds->createApiTraffic($traffic_data);
			} else {
				$response_status = -1;
				$error = array('code' => 100003, 'error' => '缺少API系统参数');
			}
			
			if ($response_status !== 0) {
				$dispatcher->forward ( array (
						'controller' => 'index',
						'action' => 'error',
						'params' => array (
								'code' => $error ['code'],
								'error' => $error ['error'] 
						) 
				) );
				return false;
			}
		}
	}
	
	/**
	 * 
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function afterExecuteRoute(Event $event, Dispatcher $dispatcher) {
		// TODO: track api response?
	}
	
	/**
	 * 
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 * @param DispatchException $exception
	 */
	public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception) {
		if (/*$this->is_api_request && */$this->traffic_id) {
			$this->ds->updateApiTrafficStatus($this->traffic_id, 7, 
					json_encode(array('code'=>$exception->getCode (), 'error'=>$exception->getMessage () . ';' . $exception->getTraceAsString()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}
		$dispatcher->forward ( array (
				'controller' => 'index',
				'action' => 'error',
				'params' => array('code'=>$exception->getCode (), 'error'=>$exception->getMessage ())
		) );
		return false;
	}
}