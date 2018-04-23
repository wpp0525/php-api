<?php

use Lvmama\Cas\Cas;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Component\RedisAdapter2;
use Lvmama\Common\Components\CasApiClient;
use Lvmama\Common\Components\CasTwoApiClient;
/*线上引用，提交代码前还原*/
use Phalcon\CLI\Console as ConsoleApp;
use Phalcon\DI\FactoryDefault\CLI as CliDI;

define('VERSION', '1.0.0');

// Using the CLI factory default services container
$di = new CliDI();

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

// Load the configuration file (if any)
if (is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
    $di->set('config', $config, true);
}


// autoloader
include APPLICATION_PATH . '/config/loader.php';

/**
 * Thrift Service To Java
 */
$di->set('tsrv', function () use ($config) {
    return \Lvmama\Common\ThriftLib\ThriftClient::getInstance($config->tsrv->toArray());
}, true);

/**
 * CAS
 */
$di->setShared('cas', function () use ($di, $config) {

    $dbsDynamic = array(
        'dblmmsys'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dblmmsys->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbcore'     => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbcore->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbmodule'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbmodule->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dblvyou'    => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dblvyou->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbmsg'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbmsg->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbnewlvyou' => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewlvyou->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbqa'       => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbqa->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbtravels'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbtravels->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbseo'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbseo->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbsub'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsub->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbsem'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsem->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbpropool'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbpropool->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbvst'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbvst->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbsource'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsource->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbbaike'    => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbbaike->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbbbs'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbbbs->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbnewguide' => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewguide->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbhotel'    => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbhotel->toArray(), 'setter' => array('wait_timeout' => 86400)),
        'dbship'     => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbship->toArray(), 'setter' => array('wait_timeout' => 86400)),
    );
    if (isset($config->dbcoupon)) {
        $dbsDynamic['dbcoupon'] = array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbcoupon->toArray());
    }
    if (isset($config->lvmamadb2)) {
        $dbsDynamic['lvmama_pet'] = array('dbAdater' => 'Lvmama\Cas\Component\OracleAdapter', 'config' => $config->lvmamadb2->lvmama_pet->toArray());
    }
    /*线上配置，提交代码前还原*/
    $redis          = new RedisAdapter2($config->redis->toArray());
    $beanstalk      = new BeanstalkAdapter($config->beanstalk->toArray());
    $singletonRedis = new RedisAdapter2($config->redis->singleton->toArray());
    return new Cas($di, $dbsDynamic, $redis, $beanstalk, $singletonRedis);
});

/**
 * redis config
 */
$di->set('redisConfig', function () {
    return require APPLICATION_PATH . '/config/redisConfig.php';
}, true);

/**
 *
 */
$di->set('config', function () use ($config) {
    return $config;
});

$di->set('soapAliyun', function () use ($config) {
    try {
        return new SOAPClient('http://106.15.35.46/soap/server.php?wsdl', $config->soap->toArray());
    } catch (SOAPFault $e) {
        var_dump($e->getMessage());
    }
}, true);
$di->set('ca', function () use ($config) {
    $private_config = $config->ca->toArray();
    return new CasApiClient($private_config['api_uri'], $private_config['token_key'], $private_config['secure_key']);
}, true);

$di->set('ca2', function () use ($config) {
    $private_config = $config->ca2->toArray();
    return new CasTwoApiClient($private_config['api_uri'], $private_config['token_key'], $private_config['secure_key']);
}, true);

$di->setShared('productpoolredis', function () use ($config) {
    $redis = new RedisAdapter2($config->redis->pp_redis->toArray());
    $redis->setClient(1);
    return $redis;
});

$di->setShared('goodspoolredis', function () use ($config) {
    $redis = new RedisAdapter2($config->redis->pp_redis->toArray());
    $redis->setClient(2);
    return $redis;
});

// Create a console application
$console = new ConsoleApp();
$console->setDI($di);

$di->setShared('console', $console);

// Process the console arguments
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    // handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
