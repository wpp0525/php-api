<?php

/**
 * API Token
 * 
 * @author mac.zhao
 *
 */
class ApiToken {

	private $key;
	
	private $status;
	
	private $type;
	
	private $for;
	
	private $location;
	
	public function getStatus() {
		return $this->status;
	}
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}
	public function getKey() {
		return $this->key;
	}
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}
	public function getType() {
		return $this->type;
	}
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	public function getFor() {
		return $this->for;
	}
	public function setFor($for) {
		$this->for = $for;
		return $this;
	}
	public function getLocation() {
		return $this->location;
	}
	public function setLocation($location) {
		$this->location = $location;
		return $this;
	}
	
	static public function parse($data) {
		$token = new ApiToken();
		$token_status = $data['token_status'];
		if (!empty($data['expire_at'])
				&& ($data['token_status'] != 2)
				&& ($data['expire_at'] < time())) {
			$token_status = 2;
		}
		return $token->setKey($data['token_key'])
				->setType($data['token_type'])
				->setFor($data['client_code'])
				->setLocation($data['location'])
				->setStatus($token_status);
	}
}