<?php

require_once('../vendor/autoload.php');

$configurator = new FuzeWorks\Configurator();

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
//$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Amsterdam');
$configurator->setTempDirectory(__DIR__ . '/temp');

$container = $configurator->createContainer();

return $container;