<?php

/**
 * 游记相关接口参数
 *
 * @author mac.zhao
 */
$parameter['auth'] = array(//controller
	'checkLoginStatus' => array(//action
		'lvsessionid' => array(//parameter 2
			'input' => 'lvsessionid',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
    ),
);