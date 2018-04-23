<?php 

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Components\ApiClient;

/**
* 从Kafka中取出消息处理
*/
class ProductDestRelWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
	/**
	 * @var \Lvmama\Cas\Service\SourceProductRelDataService
	 */
	private $source_product_dest_service;
	
	function __construct($di)
	{
		$this->source_product_dest_service = $di->get('cas')->get('source_product_dest_service');
	}

	public function handle($data)
	{
		if(isset($data->err) && isset($data->payload)){
			
			$payload = json_decode($data->payload, true);

			foreach ($payload as $value) {
				var_dump($value);
				$this->source_product_dest_service->destProductRelSave($value);
			}
		}
	}

	public function error()
	{
		// TODO: Implement error() method.
	}

	public function timeOut()
	{
		// TODO: Implement timeOut() method.
		echo 'time out!';
	}

	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
}