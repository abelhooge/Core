<?php

// This autoloader provide convinient way to working with mock object
// make the test looks natural. This autoloader support cascade file loading as well
// within mocks directory.
//
// Prototype :
//
// $mock_table = new Mock_Libraries_Table(); 			// Will load ./mocks/libraries/table.php
// $mock_database_driver = new Mock_Database_Driver();	// Will load ./mocks/database/driver.php
// and so on...
function autoload($class)
{
	$dir = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;

	$fw_core = array(
		'Benchmark',
		'Config',
		'Controller',
		'Exceptions',
		'Hooks',
		'Input',
		'Lang',
		'Loader',
		'Log',
		'Model',
		'Output',
		'Router',
		'Security',
		'URI',
		'Utf8'
	);

	$fw_libraries = array(
		'Calendar',
		'Cart',
		'Driver_Library',
		'Email',
		'Encrypt',
		'Encryption',
		'Form_validation',
		'Ftp',
		'Image_lib',
		'Javascript',
		'Migration',
		'Pagination',
		'Parser',
		'Profiler',
		'Table',
		'Trackback',
	   	'Typography',
		'Unit_test',
		'Upload',
	   	'User_agent',
		'Xmlrpc',
		'Zip'
	);

	$fw_drivers = array('Session', 'Cache');

	if (strpos($class, 'Mock_') === 0)
	{
		$class = strtolower(str_replace(array('Mock_', '_'), array('', DIRECTORY_SEPARATOR), $class));
	}
	elseif (strpos($class, 'FW_') === 0)
	{
		$subclass = substr($class, 3);

		if (in_array($subclass, $fw_core))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		elseif (in_array($subclass, $fw_libraries))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR;
			$class = ($subclass === 'Driver_Library') ? 'Driver' : $subclass;
		}
		elseif (in_array($subclass, $fw_drivers))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR.$subclass.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		elseif (in_array(($parent = strtok($subclass, '_')), $fw_drivers)) {
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR.$parent.DIRECTORY_SEPARATOR.'drivers'.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		elseif (preg_match('/^FW_DB_(.+)_(.+)_(driver|forge|result|utility)$/', $class, $m) && count($m) === 4)
		{
			$driver_path = 'Core'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'drivers'.DIRECTORY_SEPARATOR;
			$dir = $driver_path.$m[1].DIRECTORY_SEPARATOR.'subdrivers'.DIRECTORY_SEPARATOR;
			$file = $dir.$m[1].'_'.$m[2].'_'.$m[3].'.php';
		}
		elseif (preg_match('/^FW_DB_(.+)_(driver|forge|result|utility)$/', $class, $m) && count($m) === 3)
		{
			$driver_path = 'Core'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'drivers'.DIRECTORY_SEPARATOR;
			$dir = $driver_path.$m[1].DIRECTORY_SEPARATOR;
			$file = $dir.$m[1].'_'.$m[2].'.php';
		}
		elseif (strpos($class, 'FW_DB') === 0)
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR;
			$file = $dir.str_replace(array('FW_DB','active_record'), array('DB', 'active_rec'), $subclass).'.php';
		}
		else
		{
			$class = strtolower($class);
		}
	}

	$file = isset($file) ? $file : $dir.$class.'.php';

	if ( ! file_exists($file))
	{
		return FALSE;
	}

	include_once($file);
}