<?php
$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs ( array (
		$config->application->appDir . 'services/',
		$config->application->appDir . 'tasks/',
		$config->application->libraryDir . 'JPush/Model/',
) )->registerNamespaces ( array (
		'Predis' => $config->application->libraryDir . 'Predis/',
		'Pheanstalk' => $config->application->libraryDir . 'Pheanstalk/',
		'JPush\Model' => $config->application->libraryDir . 'JPush/Model/',
		'JPush' => $config->application->libraryDir . 'JPush/',
		'Monolog' => $config->application->libraryDir . 'Monolog/',
		'Psr\Log' => $config->application->libraryDir . 'Psr/',
		'Httpful' => $config->application->libraryDir . 'Httpful/',
		'Lvmama' => $config->application->sourceDir . 'Lvmama/',
		'Baidusearch' => $config->application->sourceDir . 'Baidusearch/',
		'Semsearch' => $config->application->sourceDir . 'Semsearch/',
		'Ansible' => $config->application->sourceDir . 'Ansible/',
) )->register ();

require_once $config->application->libraryDir.'Swift/swift_required.php';
