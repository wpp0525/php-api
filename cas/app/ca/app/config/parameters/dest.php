<?php
$parameter['dest'] = array(
	'getAppDestDetail' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true,
		),
		'num' => array(
			'input' => 'num',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => false,
		),
		'uid' => array(
			'input' => 'uid',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => false,
		)
	),
	'getAppDestListDetail' => array(
		'dest_ids' => array(
			'input' => 'dest_ids',
			'default' => '',
			'rule' => '',
			'required' => true,
		)
	),
	'wapGetPoiDataById' => array(
		'poiId' => array(
			'input' => 'poiId',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true,
		),
	),
	'getTransportByDest' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'type' => array(
			'input' => 'type',
			'default' => '',
			'rule' => '',
			'required' => false,
		)
	),
	'getDestByType' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'type' => array(
			'input' => 'type',
			'default' => '',
			'rule' => '',
			'required' => true,
		),
		'page' => array(
			'input' => 'page',
			'default' => 1,
			'rule' => '',
			'required' => false,
		),
		'pageSize' => array(
			'input' => 'pageSize',
			'default' => 15,
			'rule' => '',
			'required' => false,
		)
	),
	'getSubDestIdByDestId' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getAddress' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getPoiThem' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getContactById' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getTimeById' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getSuggestTimeById' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '',
			'required' => true
		)
	),
	'getTicketById' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getPicsByDest' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		),
		'uid' => array(
			'input' => 'uid',
			'default' => '0',
			'rule' => '',
			'required' => false
		),
		'page' => array(
			'input' => 'page',
			'default' => '1',
			'rule' => '^\d+$',
			'required' => false
		),
		'pageSize' => array(
			'input' => 'pageSize',
			'default' => '15',
			'rule' => '^\d+$',
			'required' => false
		),
	),
	'getNewPicsByDest' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		),
		'page' => array(
			'input' => 'page',
			'default' => '1',
			'rule' => '^\d+$',
			'required' => false
		),
		'pageSize' => array(
			'input' => 'pageSize',
			'default' => '15',
			'rule' => '^\d+$',
			'required' => false
		),
	),
	'getSummaryById' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getTripList' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getBaseIdByDestId' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getDestById' => array(
		'id' => array(
			'input' => 'id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getDestParentsByIds' => array(
		'dest_ids' => array(
			'input' => 'dest_ids',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'filter_type' => array(
			'input' => 'filter_type',
			'default' => '',
			'rule' => '',
			'required' => true
		),
	),
	'getDestsByIds' => array(
		'dest_ids' => array(
			'input' => 'dest_ids',
			'default' => '',
			'rule' => '',
			'required' => true
		)
	),
	'getNearestDests' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'dest_type' => array(
			'input' => 'dest_type',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'num' => array(
			'input' => 'num',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'need_self' => array(
			'input' => 'need_self',
			'default' => '',
			'rule' => '',
			'required' => false
		)
	),
	'getDestsBySubjectIds' => array(
		'subject_ids' => array(
			'input' => 'subject_ids',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '',
			'rule' => '^\d+$',
			'required' => true
		),
		'num' => array(
			'input' => 'num',
			'default' => '',
			'rule' => '^\d+$',
			'required' => true
		)
	),
	'getRecommendDests' => array(
		'identity' => array(
			'input' => 'identity',
			'default' => '',
			'rule' => '',
			'required' => true
		),
		'recom_name' => array(
			'input' => 'recom_name',
			'default' => '',
			'rule' => '',
			'required' => false
		),
		'per_num' => array(
			'input' => 'per_num',
			'default' => '',
			'rule' => '',
			'required' => false
		),
	),
	'getDistrictIdByDestId' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true,
		),
	),
	'getDestDistrictNav' => array(
		'type' => array(
			'input' => 'type',
			'default' => '0',
			'rule' => '',
			'required' => false,
		),
	),
	'getDestChildList' => array(
		'dest_id' => array(
			'input' => 'dest_id',
			'default' => '0',
			'rule' => '^\d+$',
			'required' => true,
		),
	),
);