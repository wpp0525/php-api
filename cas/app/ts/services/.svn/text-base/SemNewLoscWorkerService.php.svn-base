<?php

use \Baidusearch\Keyword\KeywordService;
use \Baidusearch\Keyword\UpdateWordRequest;

/**
 * kafka消息队列 Worker服务类
 *
 * @author libiying
 *
 */
class SemNewLoscWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface {

	/**
	 * @var \Lvmama\Cas\Service\SemKeywordBaseDataService
	 */
	private $keyword;

	/**
	 * @var \Lvmama\Common\ThriftLib\ThriftClient
	 */
	private $client;

	public function __construct($di) {
		$this->keyword = $di->get('cas')->get('sem_keyword_service');
		$this->client = $di->get('tsrv');
	}

	public function handle($data)
	{
		echo date('Y-m-d H:i:s', time()) . " payload：" . $data->payload . "\n";

		$payload = json_decode($data->payload, true);
		$type = $payload['type'];
		$keywords = $payload['keywords'];

		if($type == 'makeNew'){
			$this->makeNew($keywords);
		}else if($type == 'replaceOld'){
			$this->replaceOld($keywords);
		}
	}

	private function makeNew($keywords){
		$code1 = '1001';
		$code2 = 'sosuo';
		$name2 = '搜索新';

		foreach ($keywords as $keyword){
			$params = array(
				'code1' => $code1,
				'code2' => $code2,
				'name2' => $name2,
				'name3' => $keyword['keyword'],
				'channelComment' => "keywordId:" . $keyword['keywordId'],
			);
			$res = $this->client->exec("order/autoCreateChannel", array('params' => json_encode($params)));

			$new_losc = null;
			if($res){
				$new_losc = $res['msg'];
				if($new_losc == '-7'){
					$params['name3'] = $keyword['keyword'] . "-" . $keyword['keywordId'];
					$res = $this->client->exec("order/autoCreateChannel", array('params' => json_encode($params)));
					$new_losc = $res['msg'];
				}
				if(intval($new_losc) > 0) {
					$this->keyword->update($keyword['keywordId'], array('new_losc' => $new_losc));
					echo date('Y-m-d H:i:s', time()) . " update：" . $keyword['keywordId'] . "-" . $new_losc . "\n";
				}else{
					echo date('Y-m-d H:i:s', time()) . " 报错：" . $new_losc . "\n";
				}
			}
		}
	}

	private function replaceOld($keywords0){
		$keywords_count = count($keywords0);
		$keywords_num = 0;
		$datas = array();
		$kws = array();
		$format_kws = array();

		foreach ($keywords0 as $keyword){
			$format_kws[$keyword['userId']][] = $keyword;
		}

		foreach ($format_kws as $userId => $keywords){

			$service = new KeywordService();
			$service->setAuthHeader(\Baidusearch\Account::getAuthHeader($userId));
			$request = new UpdateWordRequest();

			//3 更新百度关键词losc信息
			foreach ($keywords as $keyword) {
				$keywords_num ++;

				if($keyword['new_losc']){//如果没有新的losc，则不做任何更新
					$data = new stdClass();
					$data->keywordId = $keyword['keywordId'];
					$data->pcDestinationUrl = str_replace("losc=" . $keyword['losc'], "losc=" . $keyword['new_losc'] , $keyword['pcDestinationUrl']);
					$data->mobileDestinationUrl = str_replace("losc=" . $keyword['losc'], "losc=" . $keyword['new_losc'] , $keyword['mobileDestinationUrl']);
					$datas[] = $data;

					$data->losc = $keyword['new_losc'];
					$kws[] = (array)$data;
				}
				if($keywords_num == $keywords_count){

					//更新百度
					$request->setKeywordTypes($datas);
					$response = $service->updateWord($request);
					$head = $service->getJsonHeader();
					echo date('Y-m-d H:i:s', time()) . " response:" . json_encode($response) . " head:" . json_encode($head) . "\n";
					if(isset($head->desc) && $head->desc == 'success'){
						//更新mysql
						$this->keyword->saveBatch($kws);
					}
					$kws = array();
					$datas = array();
				}
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
		echo 'alive!';
	}

}