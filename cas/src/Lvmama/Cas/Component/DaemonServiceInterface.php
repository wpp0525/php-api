<?php

namespace Lvmama\Cas\Component;

/**
 * 守护进程服务类接口
 *
 * @author mac.zhao
 *        
 */
interface DaemonServiceInterface {
	
	/**
	 * 处理
	 * 
	 * @param string $timestamp 时间戳
	 * @param string $flag 进程标记
	 */
	public function process($timestamp = null, $flag = null);
	
	/**
	 * 停止
	 * 
	 * @param string $timestamp 时间戳
	 * @param string $flag 进程标记
	 */
	public function shutdown($timestamp = null, $flag = null);
}