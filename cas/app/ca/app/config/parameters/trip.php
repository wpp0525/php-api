<?php

/**
 * 游记相关接口参数
 *
 * @author mac.zhao
 */
$parameter['trip'] = array(//controller
	'updateInfo' => array(//action
		'tripid' => array(//parameter 1
			'input' => 'tripid',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'title' => array(//parameter 2
			'input' => 'title',
			'default' => '',
			'rule' => '',
			'required' => false,
		),
		'audit' => array(//parameter 3
			'input' => 'audit',
			'default' => '0',
			'rule' => '',
			'required' => false,
		),
		'userStatus' => array(//parameter 4
			'input' => 'userStatus',
			'default' => '0',
			'rule' => '',
			'required' => false,
		),
	),
	'setTripData' => array(//action
	),
	'getTripList' => array(//action
		'dest_id' => array(//parameter 1
			'input' => 'dest_id',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'pn'=>array(
			'input' => 'pn',
			'default' => '',
			'rule' => '',
			'required' => false,
		),
		'ps'=>array(
			'input' => 'ps',
			'default' => '',
			'rule' => '',
			'required' => false,
		),
		'limit'=>array(
			'input' => 'limit',
			'default' => '',
			'rule' => '',
			'required' => false,
		)
	),
	'checkTrip' => array(//action
        'trip_id' => array(//parameter 1
            'input' => 'trip_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
	),
);