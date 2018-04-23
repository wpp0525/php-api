<?php
use Lvmama\Cas\Cas;
use Lvmama\Cas\Component\BeanstalkAdapter;
//use Phalcon\Mvc\Url as UrlResolver;
//use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Lvmama\Cas\Component\RedisAdapter2;
use Lvmama\Cas\Component\UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

/**
 * Connection DB
 */
$di->set('db', function () use ($config) {
    return new PdoMysql($config->dbsystem->master->toArray());
});
/**
 * Router
 */
$di->set('router', function () {
    return require __DIR__ . '/router.php';
}, true);

/**
 * Parameter
 */
$di->set('parameter', function () {
    return require __DIR__ . '/parameter.php';
}, true);
/**
 * Setting up the view component
 */
$di->set('view', function () {
    $view = new View();
    $view->disable()->disableLevel(View::LEVEL_NO_RENDER);

    return $view;
}, true);

/**
 * CAS
 */
$di->setShared('cas', function () use ($di, $config) {

    $dbsDynamic = array(
        'dblmmsys'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dblmmsys->toArray()),
        'dbcore'     => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbcore->toArray()),
        'dbmodule'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbmodule->toArray()),
        'dblvyou'    => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dblvyou->toArray()),
        'dbmsg'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbmsg->toArray()),
        'dbnewlvyou' => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewlvyou->toArray()),
        'dbtravels'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbtravels->toArray()),
        'dbqa'       => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbqa->toArray()),
        'dbnewcms'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewcms->toArray()),
        'dbseo'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbseo->toArray()),
        'dbvst'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbvst->toArray()),
        'dbads'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbads->toArray()),
        'dbsub'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsub->toArray()),
        'dbnewmsg'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewmsg->toArray()),
        'dbhtldest'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbhtldest->toArray()),
        'dbsem'      => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsem->toArray()),
        'dbpropool'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbpropool->toArray()),
        'dbsource'   => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbsource->toArray()),
        'lvmama_pet' => array('dbAdater' => 'Lvmama\Cas\Component\OracleAdapter', 'config' => $config->lvmama_pet->toArray()),
        'dbnewguide' => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbnewguide->toArray()),
        'sctsystem'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->sctsystem->toArray()),
        'dbbaike'    => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbbaike->toArray()),
        'sctlogger'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->sctlogger->toArray()),
        'dbchannel'  => array('dbAdater' => 'Lvmama\Cas\Component\MasterSlaveDbAdapter', 'config' => $config->dbchannel->toArray()),
    );
    $redis          = new RedisAdapter2($config->redis->toArray());
    $beanstalk      = new BeanstalkAdapter($config->beanstalk->toArray());
    $singletonRedis = new RedisAdapter2($config->redis->singleton->toArray());
    return new Cas($di, $dbsDynamic, $redis, $beanstalk, $singletonRedis);
});

/**
 * Thrift Service To Java
 */
$di->set('tsrv', function () use ($config) {
    return \Lvmama\Common\ThriftLib\ThriftClient::getInstance($config->tsrv->toArray());
}, true);

/**
 * Service To Presto
 */
$di->set('presto', function () use ($config) {
    return new \Lvmama\Common\Components\PrestoClient($config->presto->toArray()['connectUrl'], $config->presto->toArray()['catalog']);
}, true);

/**
 * Dispatcher
 */
$di->set('dispatcher', function () use ($di, $config) {
    $listener = new \ApiTrafficListener($di->get('cas'), $config->application->get('debug', false));

    $eventsManager = new EventsManager();
    $eventsManager->attach('dispatch:beforeExecuteRoute', $listener);
    $eventsManager->attach('dispatch:afterExecuteRoute', $listener);
    $eventsManager->attach('dispatch:beforeException', $listener);

    $dispatcher = new Dispatcher();
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});

/**
 * Beanstalk
 */
$di->set('beanstalk', function () use ($di, $config) {
    return new BeanstalkAdapter($di, $config->beanstalk->toArray());
});

$di->set('config', function () use ($config) {
    return $config;
});

/**
 * redis config
 */
$di->set('rediskey', function () {
    return require __DIR__ . '/rediskey.php';
}, true);

/**
 * redis ttl config
 */
$di->set('redisConfig', function () {
    return require __DIR__ . '/redisConfig.php';
}, true);
/**
 * redis config
 */
$di->set('task_code', function () {
    return require __DIR__ . '/taskCode.php';
}, true);

//redis single for Crawler
$di->set('redis', function () use ($config) {
    $redis = new \Redis();
    $redis->connect($config->redisCrawler->host, $config->redisCrawler->port);
    $redis->select($config->redisCrawler->db);
    return $redis;
}, true);
/**
 * 度假内链阿里云机器同步数据
 * 请将客户机php.ini上soap的缓存功能关闭
 */
$di->set('soapAliyun', function () use ($config) {
    try {
        return new SoapClient('http://106.15.35.46/soap/server.php?wsdl', $config->soap->toArray());
    } catch (SoapFault $e) {
        var_dump($e->getMessage());
    }
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
