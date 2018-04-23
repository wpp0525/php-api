<?php

/**
 * 游记相关接口参数
 *
 * @author sx
 */
$parameter['pktravel'] = array(//controller
	'getTravel' => array(//action
		'dest_id' => array(//parameter 1
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '',
			'required' => true,
		),
		'page' => array(//parameter 2
			'input' => 'page',
			'default' => '1',
			'rule' => '',
			'required' => false,
		),
		'pageSize' => array(//parameter 3
			'input' => 'pageSize',
			'default' => '15',
			'rule' => '',
			'required' => false,
		),
	)
);