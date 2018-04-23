<?php

/**
 * 游记相关接口参数
 *
 * @author mac.zhao
 */
$parameter['pkdest'] = array(//controller
	'getDestById' => array(//action
		'id' => array(//parameter 1
			'input' => 'id',
			'default' => '0',
			'rule' => '',
			'required' => true,
		)
	),
	'getImgCount' => array(//action
		'dest_id' => array(//parameter 1
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '',
			'required' => true,
		),
	),
	'getImg' => array(//action
		'dest_id' => array(//parameter 1
			'input' => 'dest_id',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'page'=>array(
			'input' => 'page',
			'default' => '1',
			'rule' => '',
			'required' => false,
		),
		'pageSize' => array(
			'input' => 'pageSize',
			'default' => '15',
			'rule' => '',
			'required' => false,
		),
		'uid' => array(
			'input' => 'uid',
			'default' => '0',
			'rule' => '',
			'required' => false,
		)
	),
	'getRecommendViewspot' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '',
			'required' => true,
		),
		'limit' => array(
			'input' => 'limit',
			'default' => '7',
			'rule' => '',
			'required' => false,
		)
	)
);