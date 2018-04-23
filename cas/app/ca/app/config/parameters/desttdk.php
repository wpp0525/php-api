<?php
$parameter['desttdk'] = array(
	'getDestTdk' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '',
			'rule' => '^\d+$',
			'required' => true,
		),
		'tdk_key' => array(
			'input' => 'tdk_key',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'current' => array(
			'input' => 'current',
			'default' => '',
			'rule' => '',
			'required' => false,
		)
	),
);