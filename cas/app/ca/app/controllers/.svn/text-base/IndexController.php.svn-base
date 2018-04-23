<?php
use Lvmama\Common\Utils\UCommon;

/**
 * 默认控制器
 * 
 * @author mac.zhao
 *
 */
class IndexController extends ControllerBase {
	
	public function indexAction() {
		
		// redis cluster no pipeline
		/*
		$time_start = $this->microtime_float();
		
		for($i=0; $i<50; $i++) {
			// node 1
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000004761');
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000007483');
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000011494');
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000019786');
			
			// node 3
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000000269');
			$this->di->get('cas')->getRedis()->hGetAll('productpool:0010000003730');
		}
		
		$time_end = $this->microtime_float();
		$time = $time_end - $time_start;
		echo 'redis cluster no pipeline time : ' . $time * 1000 . ' ms';
		echo '<br/>';
		
		// redis singleton with pipeline
		
		$time_start = $this->microtime_float();
		
		$keys = array(
			// node 1
			'productpool:0010000004761',
			'productpool:0010000007483',
			// node 3
			'productpool:0010000000269',
			'productpool:0010000003730',
			// node 1
			'productpool:0010000011494',
			'productpool:0010000019786',
		);
		
		// key 根据 node 分组
		$nodes = array();
		foreach ($keys as $value) {
			$nodes[UCommon::calRedisNode($value)][] = $value;
		}
		
		// 3 个 redis node
		for($i = 1; $i <= 3; $i++) {
		
			if(!empty($nodes[$i])) { // 无 key 的 node 不需要查询
			
				$this->di->get('cas')->getRedis($i)->pipeline();
				
				for($j = 0; $j < 50; $j++) {
					// node 1
					foreach ($nodes[$i] as $value) {
						$this->di->get('cas')->getRedis($i)->hGetAll($value);
					}
				}
				
				$this->di->get('cas')->getRedis($i)->exec();
			
			}
		
		}
		
		$time_end = $this->microtime_float();
		$time = $time_end - $time_start;
		echo 'redis singleton with pipeline time : ' . $time * 1000 . ' ms';
		echo '<br/>';
		
		exit;
		
		
		
		
		$this->di->get('cas')->getRedis(3)->pipeline();
		
		for($i=0; $i<50; $i++) {
			// node 3
			$this->di->get('cas')->getRedis(3)->hGetAll('productpool:0010000000269');
			$this->di->get('cas')->getRedis(3)->hGetAll('productpool:0010000003730');
		}
		
		$this->di->get('cas')->getRedis(3)->exec();
		
		$time_end = $this->microtime_float();
		$time = $time_end - $time_start;
		echo 'redis singleton with pipeline time : ' . $time * 1000 . ' ms';
		echo '<br/>';
		
		exit;*/
		return $this->jsonResponse(array('success' => 'welcome'));
	}
	
	
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * 404 Page
	 */
	public function missingAction() {
		$this->view->disable();
		$this->response->setStatusCode(404, 'Not Found');
		$this->response->setContentType('application/json', 'utf-8');
		$this->response->setJsonContent(array('error'=>100001, 'error_description'=>'请求的API不存在'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		return $this->response;
	}
	
	/**
	 * ERROR
	 * @return Ambigous <\Phalcon\Http\Response, \Phalcon\HTTP\ResponseInterface>
	 */
	public function errorAction() {
// 		$params = $this->dispatcher->getParams();
// 		return $this->errorResponse($params['code'], $params['error']);
// 	    $content = array(
//     		'result' => 1,
// 	    );
// 	    $this->_successResponse($content);
	}
}
