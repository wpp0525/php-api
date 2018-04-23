<?php
$loader = new \Phalcon\Loader ();
$commonPath ='';
if(file_exists(dirname(__FILE__)."/../../../../../../lib/Lvmama/Common")){
	$commonPath = realpath(dirname(__FILE__)."/../../../../../../lib/Lvmama/Common");
}elseif (file_exists(dirname(__FILE__)."/../../../../../../php-lib/Lvmama/Common")){
	$commonPath = realpath(dirname(__FILE__)."/../../../../../../php-lib/Lvmama/Common");
}else{
	throw new \Exception("Please checkout php-lib project, Set it with your project in one directory, Name required is 'php-lib' or 'lib' !!!");
}
/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs ( array (
		$config->application->appDir . 'component/',
		$config->application->appDir . 'controllers/',
		$config->application->appDir . 'listeners/',
) )->registerNamespaces ( array (
		'Phalcon' => $config->application->incubatorDir,
		'Predis' => $config->application->libraryDir . 'Predis/',
		'Pheanstalk' => $config->application->libraryDir . 'Pheanstalk/',
		'Lvmama' => $config->application->sourceDir . 'Lvmama/',
		'Lvmama\Common' => $commonPath,
	'Baidusearch' => $config->application->sourceDir . 'Baidusearch/',
	'Ansible' => $config->application->sourceDir . 'Ansible/',
) )->register ();
