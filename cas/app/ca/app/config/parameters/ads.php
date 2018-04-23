<?php

$parameter['ads'] = array(
    'bannerList' => array(
        'page' => array(
            'input' => 'page',
            'default' => 1,
            'rule' => '',
            'required' => false,
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => 10,
            'rule' => '',
            'required' => false,
        )
    ),
    'banner' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
        'show_detail' => array(
            'input' => 'show_detail',
            'default' => 1,
            'rule' => '',
            'required' => false,
        ),
        'show_property' => array(
            'input' => 'show_property',
            'default' => '0',
            'rule' => '',
            'required' => false,
        ),
    ),
    'bannerDetail' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'banner_id' => array(
            'input' => 'banner_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
    ),
    'bannerProperty' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
    ),
    'zoneList' => array(
        'page' => array(
            'input' => 'page',
            'default' => 1,
            'rule' => '',
            'required' => false,
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => 10,
            'rule' => '',
            'required' => false,
        )
    ),
    'zone' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
        'show_property' => array(
            'input' => 'show_property',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
    ),
    'zoneProperty' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
    ),
    'zoneCampaign' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
    ),
    'campaign' => array(
        'zone_id' => array(
            'input' => 'zone_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'status' => array(
            'input' => 'status',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'campaignDel' => array(
        'campaign_id' => array(
            'input' => 'campaign_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
    ),
    'campaignReign' => array(
        'zone_id' => array(
            'input' => 'zone_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
        'match_url' => array(
            'input' => 'match_url',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'zoneElection' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'zone_id' => array(
            'input' => 'zone_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
        'type' => array(
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'campaign_cycle' => array(
            'input' => 'campaign_cycle',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'propertyList' => array(
        'page' => array(
            'input' => 'page',
            'default' => 1,
            'rule' => '',
            'required' => false,
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => 10,
            'rule' => '',
            'required' => false,
        ),
        'id_not_in' => array(
            'input' => 'id_not_in',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'property' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        ),
    ),
);