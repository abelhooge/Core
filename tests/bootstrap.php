<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$configurator = new FuzeWorks\Configurator();

// Set directories
$parameters = array(
		'appDir' => dirname(__FILE__) . '/application',
		'wwwDir' => dirname(__FILE__) . '/www'
	);
$configurator->setParameters($parameters);

$configurator->setTimeZone('Europe/Amsterdam');
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->setLogDirectory(__DIR__ . '/temp');

$container = $configurator->createContainer();

return $container;