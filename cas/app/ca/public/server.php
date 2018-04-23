<?php
    error_reporting(0);
try {

    /**
     * Read the configuration
     */
    $config = include __DIR__ . "/../app/config/config.php";

    /**
     * Read auto-loader
     */
    include __DIR__ . "/../app/config/loader.php";

    /**
     * Read parameter
     */
    include __DIR__ . "/../app/config/parameter.php";

    /**
     * Read responst status code
     */
    include __DIR__ . "/../app/config/responseStatus.php";

    /**
     * Read services
     */
    include __DIR__ . "/../app/config/services.php";

    /**
     * Handle the Server
     */
	$worker = new \Lvmama\Common\ThriftLib\ThriftWorker($config->thrift->toArray());
	$worker->start();

} catch (\Exception $e) {
    echo $e->getMessage() . '; ' . $e->getTraceAsString();
}
