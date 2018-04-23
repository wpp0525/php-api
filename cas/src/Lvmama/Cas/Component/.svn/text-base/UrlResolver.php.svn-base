<?php

namespace Lvmama\Cas\Component;

use Phalcon\Mvc\Url;

/**
 * URL 处理
 *
 * @author mac.zhao
 *        
 */
class UrlResolver extends Url {
	
	/**
	 * @see \Phalcon\Mvc\Url::get()
	 */
	public function get($uri = null, $args = null, $local = null) {
		if (is_string($uri))
			$uri = ltrim($uri, '/');
		return parent::get($uri, $args, $local);
	}
	
	/**
	 * @see \Phalcon\Mvc\Url::setBaseUri()
	 */
	public function setBaseUri($baseUri) {
		if (empty($baseUri))
			$baseUri = '/';
		if (substr($baseUri, -1) != '/')
			$baseUri .= '/';
		parent::setBaseUri($baseUri);
		return $this;
	}

}