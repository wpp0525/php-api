<?php

/**
 * kafka消息队列 Worker服务类
 *
 * @author libiying
 *
 */
class CanalEsWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface {


	public function __construct($di) {

	}

	public function handle($data)
	{
		// TODO: Implement handle() method.
		var_dump($data);
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

}