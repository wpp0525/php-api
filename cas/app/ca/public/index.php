<?php

error_reporting(0);
// ini_set("display_errors","On");
//error_reporting(E_ALL);



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
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);
    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '; ' . $e->getTraceAsString();
}
