<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\CasApiClient;

/**
 * API 数据服务类
 *
 * @author mac.zhao
 *        
 */
class ApiDataService extends DataServiceBase {
	
	/**
	 * @example Misc::vnsprintf(ApiDataService::key_pattern_api_token, array('token'=>$key));
	 * @var string API Token 的Redis键名模式
	 */
	const key_pattern_api_token = "sys:api:token:%token\$s";
	
	/**
	 * @var int Token不存在
	 */
	const ERROR_TOKEN_MISSING = 101001;
	/**
	 * @var int Token过期
	 */
	const ERROR_TOKEN_EXPIRED = 101002;
	/**
	 * @var int Token禁用
	 */
	const ERROR_TOKEN_DISABLED = 101003;
	/**
	 * @var int Token签名无效
	 */
	const ERROR_TOKEN_SIGN_INVALID = 101004;
	/**
	 * @var int Token类型不匹配
	 */
	const ERROR_TOKEN_TYPE_MISMATCH = 101005;
	/**
	 * @var int Token请求IP非法
	 */
	const ERROR_TOKEN_IP_ILLEGAL = 101006;
	/**
	 * @var int Token请求时间戳无效
	 */
	const ERROR_TOKEN_TIMESTAMP_INVALID = 101007;
	/**
	 * @var int 请求API不存在
	 */
	const ERROR_API_NOT_FOUND = 100001;
	/**
	 * @var int 无API访问权限
	 */
	const ERROR_API_NOT_ALLOW = 100002;
	
	/**
	 * 获取Token信息
	 * 
	 * @param string $key Token Key
	 */
	public function getTokenInfo($key) {
		$redis_key = Misc::vnsprintf(self::key_pattern_api_token, array('token'=>$key));
		$token = $this->getRedis()->retrive(array('key'=>$redis_key, 'ttl'=>3600), function () use ($key){
			$sql = "SELECT * FROM sys_api_token WHERE token_key = :key";
			$result = $this->getAdapter()->query($sql, array('key'=>$key));
			$result->setFetchMode(\PDO::FETCH_ASSOC);
			return $result->numRows () > 0 ? $result->fetch () : null;
		});
		return $token;
	}
	
	/**
	 * Token 检验
	 * 
	 * @todo 通过Redis暂存（缓存）校验结果，一次校验成功后一段时间内（比如10分钟）不再重复校验而是直接提取Redis的校验结果
	 * 
	 * @param string $key Token Key
	 * @param array $data 校验数据
	 * @param string $type 校验类型
	 * @param string $ip 校验IP地址 
	 */
	public function checkToken($key, array $data = null, $type = null, $ip = null) {
		if ($token = $this->getTokenInfo($key)) {
			if ((intval($token['token_status']) === 2) || ($token['expire_at'] && $token['expire_at'] < time())) {
				throw new \Exception('Token 过期', self::ERROR_TOKEN_EXPIRED);
			} elseif (intval($token['token_status']) === 1) {
				throw new \Exception('Token 禁用', self::ERROR_TOKEN_DISABLED);
			} elseif (!is_null($type) && $type != $token['token_type']) {
				throw new \Exception('Token 请求类型不匹配', self::ERROR_TOKEN_TYPE_MISMATCH);
			} elseif (!is_null($ip) && !empty($token['bind_ip']) && $ip != $token['bind_ip']) {
				throw new \Exception('Token 请求IP非法', self::ERROR_TOKEN_IP_ILLEGAL);
			}
			
			if (is_null($data)) { // $data为空时不校验数据
				//return true;
			} elseif (($data['timestamp'] > time() + 300) || ($data['timestamp'] < time() - 300)) { // 时效±300秒
				throw new \Exception('Token 时间戳无效', self::ERROR_TOKEN_TIMESTAMP_INVALID);
			} elseif ($data['sign'] === Misc::sign(CasApiClient::PATTERN_TOKEN_SIGN, $data, $token['secure_key'])) {
				//return true;
			} else {
				throw new \Exception('Token 签名无效', self::ERROR_TOKEN_SIGN_INVALID);
			}
			return $token;
		} else {
			throw new \Exception('Token 不存在', self::ERROR_TOKEN_MISSING);
		}
	}
	
	/**
	 * 创建API流量记录
	 * 
	 * @param array $data
	 * 
	 * @return integer lastIntertId()
	 */
	public function createApiTraffic(array $data) {
		$data['track_at'] = time();
		return $this->getAdapter()->insert('sys_api_traffic', array_values($data), array_keys($data));
	}
	
	/**
	 * 更新API流量记录状态
	 * 
	 * @param integer $traffic_id
	 * @param integer $status
	 */
	public function updateApiTrafficStatus($traffic_id, $status, $response = null) {
		return $this->getAdapter()->update('sys_api_traffic', 
				array('response_status', 'response_data') , 
				array($status, $response), 'id = ' . $traffic_id);
	}
}