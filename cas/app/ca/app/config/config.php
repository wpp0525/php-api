<?php
return new \Phalcon\Config ( array (
'dbchannel'  =>  array (
   'master'  =>  array (
	'host'  =>  '192.168.0.139',
	'username'  =>  'root',
	'password'  =>  '123456',
	'dbname'  =>  'lmm_channel',
	'charset'  =>  'utf8',
	'persistent'  =>  false,
),
    'slaves'  =>  array (
	array (
	'host'  =>  '192.168.0.139',
	'username'  =>  'root',
	'password'  =>  '123456',
	'dbname'  =>  'lmm_channel',
	'charset'  =>  'utf8',
	'persistent'  =>  false,
	),
   ),
),
    'dbnewguide' => array (
                'master' => array (
                        'host' => '192.168.0.139',
                        'username' =>'root',
                        'password' =>'123456',
                        'dbname' => 'lmm_new_guide',
                        'charset' => 'utf8',
                        'persistent' => false
                ),
                'slaves' => array (
                        array (
                                'host' => '192.168.0.139',
                                'username' =>'root',
                                'password' => '123456',
                                'dbname' => 'lmm_new_guide',
                                'charset' => 'utf8',
                                'persistent' => false
                        )
                )
    ),
    'dbbaike' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_baike',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_baike',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'sctlogger' => array (
                'master' => array (
                        'host' => '192.168.0.139',
                        'username' => 'root',
                        'password' => '123456',
                        'dbname' => 'lmm_sct_logs',
                        'charset' => 'utf8',
                        'persistent' => false
                ),
                'slaves' => array (
                        array (
                                'host' => '192.168.0.139',
                                'username' => 'root',
                                'password' => '123456',
                                'dbname' => 'lmm_sct_logs',
                                'charset' => 'utf8',
                                'persistent' => false
                        )
                )
    ),
    'dblmmsys' => array (
       'master' => array (
           'host' => '192.168.0.139',
           'username' => 'root',
           'password' => '123456',
           'dbname' => 'lmm_sys',
           'charset' => 'utf8',
           'persistent' => false
       ),
       'slaves' => array (
           array (
               'host' => '192.168.0.139',
               'username' => 'root',
               'password' => '123456',
               'dbname' => 'lmm_sys',
               'charset' => 'utf8',
               'persistent' => false
           )
       )
    ),
    'dbbaike' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_baike',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_baike',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'sctsystem' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_cms_test',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_cms_test',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbcore' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_core',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_core',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dblvyou' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_lvyou',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_lvyou',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbmodule' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_module',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_module',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbmsg' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_message',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_message',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbnewlvyou' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_destination',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_destination',
                'charset' => 'utf8',
                'persistent' => false
            )
        ),
    ),
    'dbtravels' => array (
        'master' => array (
        'host' => '192.168.0.139',
	'username' => 'root',
	'password' => '123456',
	'dbname' => 'lmm_travels',
        'charset' => 'utf8mb4',
	    'persistent' => false 
	),
	'slaves' => array (
	    array (
		'host' => '192.168.0.139',
		'username' => 'root',
		'password' => '123456',
		'dbname' => 'lmm_travels',
		'charset' => 'utf8mb4',
		'persistent' => false
	    ),
        ),
    ),
    'dbqa' => array (
        'master' => array (
        'host' => '192.168.0.139',
        'username' => 'root',
        'password' => '123456',
        'dbname' => 'lmm_qa',
        'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_qa',
                'charset' => 'utf8',
                'persistent' => false
            ),
        ),
    ),
    'dbnewcms' => array (
        'master' => array (
                        'host' => '192.168.0.139',
                        'username' => 'root',
                        'password' => '123456',
                        'dbname' => 'lmm_cms',
                        'charset' => 'utf8',
                                'persistent' => false
                ),
                'slaves' => array (
                        array (
                                'host' => '192.168.0.139',
                                'username' => 'root',
                                'password' => '123456',
                                'dbname' => 'lmm_cms',
                                'charset' => 'utf8',
                                'persistent' => false
                        ),
                ),
    ),
    'dbseo' => array (
                'master' => array (
                        'host' => '192.168.0.139',
                        'username' => 'root',
                        'password' => '123456',
                        'dbname' => 'lmm_seo',
                        'charset' => 'utf8',
                        'persistent' => false
                ),
                'slaves' => array (
                        array (
                                'host' => '192.168.0.139',
                                'username' => 'root',
                                'password' => '123456',
                                'dbname' => 'lmm_seo',
                                'charset' => 'utf8',
                                'persistent' => false
                        )
                )
    ),
    'dbvst' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_vst_destination',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_vst_destination',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbsub' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_subject',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_subject',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbads' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_adserver',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_adserver',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbnewmsg' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_msg',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_msg',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'dbsem' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_sem',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_sem',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),

    'dbpropool' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_pp',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_pp',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),

    'dbsource' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_source',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_source',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),

    'dbhtldest' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_hotel_destaround',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_hotel_destaround',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
    'sctsystem' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'lmm_cms_test',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'lmm_cms_test',
                'charset' => 'utf8',
                'persistent' => false
            )
        )
    ),
	'lvmama_pet' => array(
	    'racs' => array(
	        array(
	            'username' => 'lvmama_pet',
	            'password' => 'hJn4B90rPO',
	        	'charset' => 'AL32UTF8',
	            'dbname' => '(DESCRIPTION =(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.0.70)(PORT = 1523)))(CONNECT_DATA =(SERVICE_NAME = lvmamadb2)(FAILOVER_MODE=(TYPE=SELECT)(METHOD=BASIC)(RETRIES=20)(DELAY=5))))',
	        ),
	        array(
	            'username' => 'lvmama_pet',
	            'password' => 'hJn4B90rPO',
	        	'charset' => 'AL32UTF8',
	            'dbname' => '(DESCRIPTION =(ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.0.70)(PORT = 1523)))(CONNECT_DATA =(SERVICE_NAME = lvmamadb2)(FAILOVER_MODE=(TYPE=SELECT)(METHOD=BASIC)(RETRIES=20)(DELAY=5))))',
	        ),
	    )
	),

//    'redis'=> array(
//        'parameters' => array('host'=>"10.200.2.86","port"=>'6379'),
//        'options' => array()
//    ),
    'redis'=> array(
        'parameters' => array(
            'tcp://10.200.2.86:7000',
            'tcp://10.200.2.86:7001',
            'tcp://10.200.2.86:7002',
            'tcp://10.200.2.87:7003',
            'tcp://10.200.2.87:7004',
            'tcp://10.200.2.87:7005',
        ),
        'phpredis-parameters' => array(
            '10.200.2.86:7000',
            '10.200.2.86:7001',
            '10.200.2.86:7002',
            '10.200.2.87:7003',
            '10.200.2.87:7004',
            '10.200.2.87:7005',
        ),
        'singleton' => array(
            'phpredis-parameters' => array(
                '1' => array (
                    'host' => '10.200.2.86',
                    'port' => '7000'
                ),
                '2' => array (
                    'host' => '10.200.2.87',
                    'port' => '7003'
                ),
                '3' => array (
                    'host' => '10.200.2.86',
                    'port' => '7001'
                ),
            ),
        ),
        'options' => array('cluster' => 'redis'),
    ),
    'beanstalk' => array(
        'host' => '10.200.2.86',
        'port' => 11300
    ),
    'elasticsearch' => array(
	'host' => '10.200.2.82',
	'port' => 9200
    ),
        'thrift' => array(
		'register_address' => '10.200.2.86:2181',
                //'server_host' => '10.200.2.85',
                'server_port' => 9090,
                'thrift_service' => 'com.lvmama.phpcas.Service',
                'thrift_impl' => 'com.lvmama.phpcas.Impl',
                'worker_num' => 4,
                'worker_log' => '/var/log/php-service.log',
        ),
        'kafka' => array(
		'msgProducer' => array(
			'brokerList' => "10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092",
			'topics' => 'input-products',
		),
                'stormExport' => array(
                    'brokerList' => "10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092",
                    'topics' => 'storm-export',
                ),
                'ruleEnginePit' => array(
	            'brokerList' => "10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092",
        	    'topics' => 'rule-engine-test',
	        ),
		'templateProducer' => array(
		    'brokerList'  => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
		    'topics'      => 'template-save',
		)
	),
	'tsrv' => array(
			'register_address' => '10.200.2.87:2181,10.200.5.169:2181,10.200.5.170:2181',
    		'thrift_timeout' => 30000,
    		'thrift_service' => 'com.lvmama.phpsrv.Service',
			'thrift_protocol' => 'compact',
	),
        'trpc' => array(
                        'register_address' => '10.200.2.87:2181,10.200.5.169:2181,10.200.5.170:2181',
                'thrift_timeout' => 4000,
                'thrift_service' => 'com.lvmama.phprpc.monitor',
                        'thrift_protocol' => 'compact',
        ),
        'presto' => array(
                        'connectUrl' => 'http://10.200.2.87:8085/v1/statement',
                'catalog' => 'hive',
        ),
    'application' => array (
        'appDir' => __DIR__ . '/../../app/',
        'sourceDir' => __DIR__ . '/../../../../src/',
        'libraryDir' => __DIR__ . '/../../../../lib/',
        'incubatorDir' => __DIR__ . '/../../../../../vendor/phalcon/incubator/Library/Phalcon/',
        'baseUri' => '/',
        'debug' => true
    )
) );
