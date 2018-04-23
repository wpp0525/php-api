<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 环境系统信息
 *
 * @author dirc.wang
 *
 */
class EnvconfigWorkerService implements DaemonServiceInterface {

  private $redis;
  private $sys_data_service;
  private $offset;
  private $config;
  private $timeout = 30;
  private $remote_ip = '10.112.4.140';

  public function __construct($di) {
    $this->redis = $di->get('cas')->getRedis();
    $this->sys_data_service = $di->get('cas')->get("sys_data_service");
    $this->config = isset($di->get('config')->envconfig) ? $di->get('config')->envconfig : array();
  }

  /**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
    $this->runRenewServerInfo();
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}

/**
 * 开启任务
 * @return void
 */
  public function runRenewServerInfo(){
    $this->reNewServerInfo();
  }

/**
 * 递归刷新
 * @return void
 */
  public function reNewServerInfo(){
    $renewredis = RedisDataService::REDIS_ENV_LIST . 'task:' . 'renew';
    $this->offset = $this->redis->incr($renewredis);//增加
    $this->offset = intval($this->offset - 1);
    $info = $this->sys_data_service->getNextServerInfo($this->offset);
    // 没有则终止线程
    if(!$info || !isset($info['ip']) || !isset($info['id'])){
      echo "\n********************end********************\n";
      exit(0);
    }
    $this->saveServerInfo($info['ip']);
    $this->reNewServerInfo();
  }

/**
 * 刷新服务器信息
 * @param  int $ip ip
 * @return [type]     [description]
 */
  public function saveServerInfo($ip){
    $Ansiblesystem = new \Ansible\classes\Ansiblesystem();
    $serverinfo = array();
    $val = $Ansiblesystem->getDfCount($ip);
    $serverinfo['dfall'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
    $val = $Ansiblesystem->getDfused($ip);
    $serverinfo['dfused'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
    $val = $Ansiblesystem->getFreetotal($ip);
    $serverinfo['freetotal'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
    $val = $Ansiblesystem->getFreeused($ip);
    $serverinfo['freeused'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
    $val = $Ansiblesystem->getPressSum($ip);
    $serverinfo['press'] = ($val !== FALSE) ? $val : '';
    $datain = array();
    if($serverinfo['freetotal'])
      $datain['mem'] = $serverinfo['freetotal'];
    if($serverinfo['freeused']){
      $datain['mem_used'] = $serverinfo['freeused'];
    }
    if($serverinfo['dfall'])
      $datain['disk'] = $serverinfo['dfall'];
    if($serverinfo['dfused']){
      $datain['disk_used'] = $serverinfo['dfused'];
    }
    $serverinfo['ip'] = $ip;
    $this->sys_data_service->editofflineServer($datain, array(
      'ip' => $ip,
    ));
    // 打印信息
    echo "\n" . $this->offset . "\n";
    print_r($serverinfo);
    echo "\n";
//推送消息
    $this->sendWaring($serverinfo);
  }

  public function sendWaring($data = array()){
    $this->sendMemWaring($data);
    $this->sendFreeWaring($data);
    $this->sendPressWaring($data);
  }

  public function sendMemWaring($serverinfo){
    $memwaring = RedisDataService::REDIS_ENV_LIST . 'serverwaring:mem';
    $dfall = isset($serverinfo['dfall']) ? $serverinfo['dfall'] : 0;
    $dfused = isset($serverinfo['dfused']) ? $serverinfo['dfused'] : 0;
    $ip = isset($serverinfo['ip']) ? $serverinfo['ip'] : 0;
    $dfline = isset($this->config->dfline) ? $this->config->dfline : 0.85;
    if($dfused && $dfall){
      if($dfused / $dfall> 0.85 ){
        if(!$this->redis->sIsMember($memwaring, $ip)){
          //如果不存在则发送
          $this->redis->sAdd($memwaring, $ip);
          // 发送邮件
          $this->sendMemMail($ip,  $dfused, $dfall);
          //发送短信
          $this->sendMemMsg($ip,  $dfused, $dfall);
        }
      }else{
        if($this->redis->sIsMember($memwaring, $ip)){
          //如果存在则发送降压
          $this->redis->sRem($memwaring, $ip);
          // 发送邮件
          $this->sendMemMail($ip,  $dfused, $dfall);
          // 发送短信
          $this->sendMemMsg($ip,  $dfused, $dfall);
        }
      }
    }
  }

  public function sendFreeWaring($serverinfo){
    $freewaring = RedisDataService::REDIS_ENV_LIST . 'serverwaring:free';
    $freetotal = isset($serverinfo['freetotal']) ? $serverinfo['freetotal'] : 0;
    $freeused = isset($serverinfo['freeused']) ? $serverinfo['freeused'] : 0;
    $ip = isset($serverinfo['ip']) ? $serverinfo['ip'] : 0;
    $freeline = isset($this->config->freeline) ? $this->config->freeline : 0.85;
    if($freeused && $freetotal){
// 大于85%时发送，压力降下来时再次通知
      if($freeused / $freetotal> $freeline ){
        if(!$this->redis->sIsMember($freewaring, $ip)){
          //如果不存在则发送
          $this->redis->sAdd($freewaring, $ip);
          // 发送邮件
          $this->sendFreeMail($ip, $freeused, $freetotal);
          // 发送短信
          $this->sendFreeMsg($ip, $freeused, $freetotal);
        }
      }else{
        if($this->redis->sIsMember($freewaring, $ip)){
          //如果存在则发送降压
          $this->redis->sRem($freewaring, $ip);
          // 发送邮件
          $this->sendFreeMail($ip, $freeused, $freetotal);
          // 发送短信
          $this->sendFreeMsg($ip, $freeused, $freetotal);
        }
      }
    }
  }

  public function sendPressWaring($serverinfo){
    $presswaring = RedisDataService::REDIS_ENV_LIST . 'serverwaring:press';
    $press = isset($serverinfo['press']) ? $serverinfo['press'] : 0;
    $border = isset($this->config->pressline) ? $this->config->pressline : 1.3;
    $ip = isset($serverinfo['ip']) ? $serverinfo['ip'] : 0;
    if($press > $border){
      if(!$this->redis->sIsMember($presswaring, $ip)){
        //如果不存在则发送
        $this->redis->sAdd($presswaring, $ip);
        // 发送邮件
        $this->sendPressMail($ip, $press);
        // 发送短信
        $this->sendPressMsg($ip, $press);
      }
    }else{
      if($this->redis->sIsMember($presswaring, $ip)){
        //如果不存在则发送
        $this->redis->sRem($presswaring, $ip);
        // 发送邮件
        $this->sendPressMail($ip, $press);
        // 发送短信
        $this->sendPressMsg($ip, $press);
      }
    }
  }

  public function sendMemMail($ip,  $dfused, $dfall){
    if(!isset($this->config->sendmail) || !$this->config->sendmail)
      return false;
    $dfline = isset($this->config->dfline) ? $this->config->dfline : 0.85;
    $dflineshow = $dfline * 100;
    if($dfused / $dfall > $dfline){
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '0b306786-3891-4f21-96b3-d6e57edff623',
        'content' => "硬盘使用情况,已使用： {$freeused}G, 总量{$freetotal}G",
        'subject' => "硬盘使用超过{$dflineshow}%,IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }else{
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '0b306786-3891-4f21-96b3-d6e57edff623',
        'content' => "硬盘使用情况,已使用： {$freeused}G, 总量{$freetotal}G",
        'subject' => "硬盘使用率回落{$dflineshow}%以下,IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }
    echo "\n硬盘消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendEmail/httpSend', 'GET', $content));
    echo "\n";
  }

  public function sendFreeMail($ip, $freeused, $freetotal){
    if(!isset($this->config->sendmail) || !$this->config->sendmail)
      return false;
    $freeline = isset($this->config->freeline) ? $this->config->freeline : 0.85;
    $freelineshow = $freeline * 100;
    if($freeused / $freetotal > $freeline){
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '0b306786-3891-4f21-96b3-d6e57edff623',
        'content' => "内存使用情况,已使用： {$freeused}G, 总量{$freetotal}G",
        'subject' => "内存使用超过{$freelineshow}%,IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }else{
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '',
        'content' => "内存使用情况,已使用： {$freeused}G, 总量{$freetotal}G",
        'subject' => "内存使用已经回落至{$freelineshow}%以下,IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }
    echo "\n内存消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendEmail/httpSend', 'GET', $content));
    echo "\n";
  }

  public function sendPressMail($ip, $press){
    if(!isset($this->config->sendpress) || !$this->config->sendpress)
      return false;
    $border = isset($this->config->pressline) ? $this->config->pressline : 1.3;
    if($press > $border){
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '0b306786-3891-4f21-96b3-d6e57edff623',
        'content' => "IP:{$ip},负载为{$press}，满值为1",
        'subject' => "负载超过{$border},IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }else{
      $content = array(
        'token' => isset($this->config->mailtoken) ? $this->config->mailtoken : '',
        'content' => "IP:{$ip},负载为{$press}，满值为1",
        'subject' => "负载已经回落至{$border}以下,IP:" . $ip,
        'tos' => isset($this->config->mailto) ? $this->config->mailto : 'wangbu@lvmama.com',
      );
    }
    echo "\n负载消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendEmail/httpSend', 'GET', $content));
    echo "\n";
  }

  public function sendMemMsg($ip,  $dfused, $dfall){
    if(!isset($this->config->sendmsg) || !$this->config->sendmsg)
      return false;
    $dfline = isset($this->config->dfline) ? $this->config->dfline : 0.85;
    $dflineshow = $dfline*100;
    if($dfused / $dfall > $dfline){
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n硬盘使用超过{$dflineshow}%\nIP:" . $ip . "\n硬盘使用情况:\n已使用： {$freeused}G, 总量{$freetotal}G",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }else{
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n硬盘使用已经回落至{$dflineshow}%以下\nIP:" . $ip . "\n硬盘使用情况:\n已使用： {$freeused}G, 总量{$freetotal}G",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }
    echo "\n硬盘消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendSMS/httpSend', 'GET', $content));
    echo "\n";
  }

  public function sendFreeMsg($ip, $freeused, $freetotal){
    if(!isset($this->config->sendmsg) || !$this->config->sendmsg)
      return false;
    $freeline = isset($this->config->freeline) ? $this->config->freeline : 0.85;
    $freelineshow = $freeline*100;
    if($freeused / $freetotal > $freeline){
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n内存使用已高于{$freelineshow}%\nIP:" . $ip . "\n内存使用情况:\n已使用： {$freeused}G, 总量{$freetotal}G",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }else{
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n内存使用已经回落至{$freelineshow}%以下\nIP:" . $ip . "\n内存使用情况:\n已使用： {$freeused}G, 总量{$freetotal}G",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }
    echo "\n内存消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendSMS/httpSend', 'GET', $content));
    echo "\n";
  }

  public function sendPressMsg($ip, $press){
    if(!isset($this->config->sendpress) || !$this->config->sendpress)
      return false;
    $border = isset($this->config->pressline) ? $this->config->pressline : 1.3;
    if($press > $border){
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n负载已高于{$border}\nIP:" . $ip . "\n负载使用情况:\n{$press}\n满值为1",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }else{
      $content = array(
        'token' => isset($this->config->msgtoken) ? $this->config->msgtoken : 'fcdbbb9a-a6f0-403c-aaab-5bbfa8b0007a',
        'content' => "\n负载已回落至{$border}\nIP:" . $ip . "\n负载使用情况:\n{$press}\n满值为1",
        'tos' => isset($this->config->msgto) ? $this->config->msgto : '18516180508',
      );
    }
    echo "\n内存消息-IP:{$ip}\n";
    print_r($this->curl('http://super.lvmama.com/channel_back/sendSMS/httpSend', 'GET', $content));
    echo "\n";
  }

    /**
  	 * 发出HTTP请求
  	 *
  	 * @param string $url
  	 * @param string $method
  	 * @param array $postfields
  	 * @param array $headers
  	 *
  	 * @return string
  	 */
  	private function curl($url, $method, $postfields = array(), $headers = array()) {
  		$ci = curl_init();
  		curl_setopt($ci, CURLOPT_USERAGENT, ' CAS Two API Client CLENT_VERSION');
  		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->timeout);
  		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
  		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
  		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
  		curl_setopt($ci, CURLOPT_HEADER, FALSE);
  		switch ($method) {
  			case 'POST':
  				curl_setopt($ci, CURLOPT_POST, TRUE);
  				curl_setopt ($ci, CURLOPT_CUSTOMREQUEST, "POST" );
  				if (!empty($postfields)) {
  					curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
  				}
  				break;
  			case 'PUT':
  				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
  				if (!empty($postfields)) {
  					curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
  				}
  			case 'DELETE':
  				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
  			default:
  				curl_setopt($ci, CURLOPT_POST, FALSE);
  				if (!empty($postfields)) {
  					$url = $url."?".http_build_query($postfields);
  				}
  		}

  		if (!empty($this->remote_ip)) {
  			$headers['ApiRemoteAddr'] = $this->remote_ip;
  		} elseif (($remote_addr = Misc::getclientip()) != 'unknown') {
  			$headers['ApiRemoteAddr'] = $remote_addr;
  		}

  		curl_setopt($ci, CURLOPT_URL, $url );
  		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
  		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

  		$this->last_http_code = null;
  		$this->last_http_info = null;
  		$this->last_request_url = null;
  		$response = curl_exec($ci);
  		$this->last_http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
  		$this->last_http_info = curl_getinfo($ci);
  		$this->last_request_url = $url;

  		curl_close ($ci);

  		return $response;
  	}

}
