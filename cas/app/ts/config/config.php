<?php
return new \Phalcon\Config ( array (
	'dbcore' => array (
		'master' => array (
	    	'host' => '192.168.0.139',
			'username' => 'root',
			'password' => '123456',
			'dbname' => 'lmm_core',
			'charset' => 'utf8',
		),
		'slaves' => array (
			array (
				'host' => '192.168.0.139',
				'username' => 'root',
				'password' => '123456',
				'dbname' => 'lmm_core',
				'charset' => 'utf8',
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
   'dbbbs' => array (
        'master' => array (
            'host' => '192.168.0.139',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'bbs_prod',
            'charset' => 'utf8',
            'persistent' => false
        ),
        'slaves' => array (
            array (
                'host' => '192.168.0.139',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'bbs_prod',
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
	'dbsub' => array (
		'master' => array (
			'host' => '192.168.0.139',
			'username' => 'root',
			'password' => '123456',
			'dbname' => 'lmm_subjects',
			'charset' => 'utf8',
			'persistent' => false 
		),
		'slaves' => array (
			array (
				'host' => '192.168.0.139',
				'username' => 'root',
				'password' => '123456',
				'dbname' => 'lmm_subjects',
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
                        ),
            ),
            
   ),
	'dbtravels' => array (
		'master' => array (
			'host' => '192.168.0.139',
			'username' => 'root',
			'password' => '123456',
			'dbname' => 'lmm_travels',
			'charset' => 'utf8',
				'persistent' => false 
		),
		'slaves' => array (
			array (
				'host' => '192.168.0.139',
				'username' => 'root',
				'password' => '123456',
				'dbname' => 'lmm_travels',
				'charset' => 'utf8',
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
            )
        )
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
	//'redis' => array(
	//	'parameters' => array('tcp://192.168.125.134'),
	//	'options' => array()
	//),
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
                    'host' => '10.200.2.86',
                    'port' => '7002'
                ),
                '3' => array (
                    'host' => '10.200.2.87',
                    'port' => '7005'
                ),
                '4' => array (
                    'host' => '10.113.2.27',
                    'port' => '6379'
                ),
                '5' => array (
                    'host' => '10.113.2.27',
                    'port' => '6379'
                ),
            ),
        ),
	'productpoolv2' => array(
            'phpredis-parameters' => array(
                '4' => array (
                    'host' => '10.113.2.27',
                    'port' => '6379'
                )
            )
        ),
	'pp_redis'            => array(
            'phpredis-parameters' => array(
                '1' => array(
                    'host' => '10.113.2.27',
                    'port' => '6379',
                ),
                '2' => array(
                    'host' => '10.113.2.27',
                    'port' => '6379',
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
	'tsrv' => array(
		'register_address' => '10.200.2.87:2181',
		'thrift_timeout' => 30000,
		'thrift_service' => 'com.lvmama.phpsrv.Service',
		'thrift_protocol' => 'compact',
		'default_method' => '',
	),
	'kafka' => array(
		'esConsume' => array(
			'groupId'                   => 'myConsumerGroup1', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.200.2.86:9092',//逗号相隔
			'topics'                    => array('canal-test'),
		),
		'vstConsume' => array(
			'groupId'                   => 'myConsumerGroup2', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
			'topics'                    => array('canal-simulation-vst'),
		),
		'inputproductConsume' => array(
			'groupId'                   => 'myConsumerGroup2', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
			'topics'                    => array('input-products'),
		),
		'testConsume' => array(
			'groupId'                   => 'testConsumerGroup2', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.112.4.17:9092',//逗号相隔
			'topics'                    => array('test1'),
		),
		'ppConsume' => array(
			'groupId'                   => 'PHP_PROD_QUERY', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.200.1.203:7091,10.200.1.203:7092,10.200.1.203:7093',//逗号相隔
			'topics'                    => array('PROD_QUERY'),
		),
                'templateConsume' => array(
                        'groupId'                   => 'templateSaveGroup', //同一个group的consume消费同一个topic（多partition情况下）
                        //'brokerList'                => '10.200.2.86:9092',//逗号相隔
			'brokerList'                => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
                        'topics'                    => array('template-save'),
                ),
		'ruleEnginePit' => array(
			'brokerList'                => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
                            'topics' => 'rule-engine-test',
                ),
		'productpoolv2' => array(
			'groupId'                   => 'myConsumerGroup1', //同一个group的consume消费同一个topic（多partition情况下）
			'brokerList'                => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',//逗号相隔
                            'topics' => array('productpoolv2'),
                ),
		'productpoolv2Ts'          => array(
          		'groupId'    => 'myConsumerGroup1', //同一个group的consume消费同一个topic（多partition情况下）
            		'brokerList' => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092', //逗号相隔
            		'topics'     => 'productpoolv2',
        	),
		'productpoolv2InfosSync' => array(
			'groupId'    => 'myConsumerGroup1',
            		'brokerList' => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092', //逗号相隔
            		'topics'     => ['canal-simulation-productpoolv2-infos-sync'],
        	),
        	'goodspoolv2InfosSync'   => array(
			'groupId'    => 'myConsumerGroup1',
            		'brokerList' => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092', //逗号相隔
            		'topics'     => ['canal-simulation-goodspoolv2-infos-sync'],
        	),
		'productDestRelConsumer' => array(
				'groupId' => 'productDestRel',
				'brokerList' => '10.200.5.169:9092,10.200.5.170:9092,10.200.2.87:9092',
				'topics' => array('product-dest-rel')
				),
		),
	'daemon'=>array(
		'tripstatistics'=>array(
			'appName'               => 'tripstatistics',
			'appDir'                => __DIR__ . '/../',
			'appDescription'        => 'CAS Trip Statistics Worker Service',
			'logLocation'           => __DIR__ . '/../logs/tripstatistics/daemon.log',
			'authorName'            => 'System Daemon',
			'authorEmail'           => 'root@127.0.0.1',
			'appPidLocation'        => __DIR__. '/../run/tripstatistics/daemon.pid',
			'sysMaxExecutionTime'   => 0,
			'sysMaxInputTime'       => 0,
			'sysMemoryLimit'        => '1024M',
			'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
			'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
		),
		'tripcomment'=>array(
			'appName'               => 'tripcomment',
			'appDir'                => __DIR__ . '/../',
			'appDescription'        => 'CAS Trip Comment Worker Service',
			'logLocation'           => __DIR__ . '/../logs/tripcomment/daemon.log',
			'authorName'            => 'System Daemon',
			'authorEmail'           => 'root@127.0.0.1',
			'appPidLocation'        => __DIR__. '/../run/tripcomment/daemon.pid',
			'sysMaxExecutionTime'   => 0,
			'sysMaxInputTime'       => 0,
			'sysMemoryLimit'        => '1024M',
			'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
			'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
		),'travel'=>array(
			'migration'=>array(
				'appName'               => 'travel',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Travel Worker Service',
				'logLocation'           => __DIR__ . '/../logs/travel/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/travel/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
			),
			'content2dest'=>array(
				'appName'               => 'travel-c2d',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Travel Content2dest Worker Service',
				'logLocation'           => __DIR__ . '/../logs/travel-c2d/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/travel-c2d/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
			),
		),
	        'travelcontent'=>array(
        		'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
	        	'appName'               => 'travelcontent',
        		'appDir'                => __DIR__ . '/../',
        		'appDescription'        => 'CAS Travel Content Worker Service',
	        	'logLocation'           => __DIR__ . '/../logs/travelcontent/daemon.log',
        		'authorName'            => 'System Daemon',
        		'authorEmail'           => 'root@127.0.0.1',
	        	'appPidLocation'        => __DIR__. '/../run/travelcontent/daemon.pid',
        		'sysMaxExecutionTime'   => 0,
        		'sysMaxInputTime'       => 0,
        		'sysMemoryLimit'        => '1024M',
        		'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
        	),
	        'travelimage'=>array(
        		'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
        		'appName'               => 'travelimage',
	        	'appDir'                => __DIR__ . '/../',
        		'appDescription'        => 'CAS Travel image Worker Service',
        		'logLocation'           => __DIR__ . '/../logs/travelimage/daemon.log',
	        	'authorName'            => 'System Daemon',
        		'authorEmail'           => 'root@127.0.0.1',
        		'appPidLocation'        => __DIR__. '/../run/travelimage/daemon.pid',
	        	'sysMaxExecutionTime'   => 0,
        		'sysMaxInputTime'       => 0,
        		'sysMemoryLimit'        => '1024M',
	        	'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
        	),
	        'tripelitetag' => array(
        		'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
        		'appName'               => 'tripelitetag',
	        	'appDir'                => __DIR__ . '/../',
        		'appDescription'        => 'CAS Trip Elite Tag Worker Service',
        		'logLocation'           => __DIR__ . '/../logs/tripelitetag/daemon.log',
	        	'authorName'            => 'System Daemon',
        		'authorEmail'           => 'root@127.0.0.1',
        		'appPidLocation'        => __DIR__. '/../run/tripelitetag/daemon.pid',
	        	'sysMaxExecutionTime'   => 0,
        		'sysMaxInputTime'       => 0,
        		'sysMemoryLimit'        => '1024M',
	        	'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
        	),
		'msg'=>array(
			'appName'               => 'msg',
			'appDir'                => __DIR__ . '/../',
			'appDescription'        => 'CAS Msg Worker Service',
			'logLocation'           => __DIR__ . '/../logs/msg/daemon.log',
			'authorName'            => 'System Daemon',
			'authorEmail'           => 'root@127.0.0.1',
			'appPidLocation'        => __DIR__. '/../run/msg/daemon.pid',
			'sysMaxExecutionTime'   => 0,
			'sysMaxInputTime'       => 0,
			'sysMemoryLimit'        => '1024M',
			'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
			'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
		),
		'timer'=>array(
			'appName'               => 'timer',
			'appDir'                => __DIR__ . '/../',
			'appDescription'        => 'CAS Timer Worker Service',
			'logLocation'           => __DIR__ . '/../logs/timer/daemon.log',
			'authorName'            => 'System Daemon',
			'authorEmail'           => 'root@127.0.0.1',
			'appPidLocation'        => __DIR__. '/../run/timer/daemon.pid',
			'sysMaxExecutionTime'   => 0,
			'sysMaxInputTime'       => 0,
			'sysMemoryLimit'        => '1024M',
			'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
			'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
		),
		'robot'=>array(
		    'pv'=>array(
				'appName'               => 'robot-pv',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot PV Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-pv/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-pv/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
            		),
		    'like'=>array(
				'appName'               => 'robot-like',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Like Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-like/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-like/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
            		),
		    'comment'=>array(
				'appName'               => 'robot-comment',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Comment Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-comment/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-comment/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
            		),
		    'msg'=>array(
				'appName'               => 'robot-msg',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Msg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-msg/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-msg/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
            		),
		),
		'history'=>array(
		    'msg'=>array(
				'appName'               => 'history-msg',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS History Msg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/history-msg/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/history-msg/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
				'appRunAsGID'			=> function_exists('posix_getegid') ? posix_getegid() : 1000,
            		),
		),
		'refreshproduct' => array(
                        'appName'               => 'refreshproduct',
                        'appDir'                => __DIR__ . '/../',
                        'appDescription'        => 'Refresh Product Data Maintenance',
                        'logLocation'           => __DIR__ . '/../logs/refreshproduct/daemon.log',
                        'authorName'            => 'System Daemon',
                        'authorEmail'           => 'root@127.0.0.1',
                        'appPidLocation'        => __DIR__. '/../run/refreshproduct/daemon.pid',
                        'sysMaxExecutionTime'   => 0,
                        'sysMaxInputTime'       => 0,
                        'sysMemoryLimit'        => '1024M',
                        'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
                        'appRunAsGID'                   => function_exists('posix_getegid') ? posix_getegid() : 1000,
                ),
		'es'=>array(
                        'appName'               => 'es',
                        'appDir'                => __DIR__ . '/../',
                        'appDescription'        => 'ElasticSearch import data Worker Service',
                        'logLocation'           => __DIR__ . '/../logs/es/daemon.log',
                        'authorName'            => 'System Daemon',
                        'authorEmail'           => 'root@127.0.0.1',
                        'appPidLocation'        => __DIR__. '/../run/es/daemon.pid',
                        'sysMaxExecutionTime'   => 0,
                        'sysMaxInputTime'       => 0,
                        'sysMemoryLimit'        => '1024M',
                        'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
                        'appRunAsGID'           => function_exists('posix_getegid') ? posix_getegid() : 1000,
                ),
	),
	'application' => array (
		'sourceDir' => __DIR__ . '/../../../src/',
		'libraryDir' => __DIR__ . '/../../../lib/',
		'appDir' => __DIR__ . '/../' 
	),
	'smsprovider'=>array(
		'provider' => 'yuntongxun',
		'options'=>array(
			'AccountSid'               	=> 'aaf98f894700d34e014713b2ad6c0401',
			'AccountToken'         	=> 'a5b648420b314389bcefb8d88e8d41a6',
			'AppId'        					=> 'aaf98f89471ea2c101471f55cd1c0038',
			'logging'          				=> true,
			'logfile'            				=> '/var/workspace/logs/sms.log',
		),
		'templates'=>array(
			'user_register'            	=> 2299,
			'user_binding'             => 2301,
			'reset_password'       => 2300,
			'giftware_ticket'         => 2302,
			'user_login'         			=> 2299,
		)
	),
	'mail' => array (
		'emailHost' 		=> 'smtp.163.com',//mail.lvmama.com
		'emailPort' 		=> '25',//''
		'emailUser' 		=> '',//dlsupport@lvmama.com
		'emailPwd' 		=> '',//P@ssw0rd
		'emailName' 	=> 'Admin'
	),
	'notice' => array (
		'app_key' 		=> '698cfaa405ebefc4757bfcbb',
		'master_secret' => '14aa5ed1f37ca88d55fb693f',
	),
) );
