<?php
// Include framework
if (!defined('FUZEPATH')) {
	define( 'FUZEPATH', dirname(__FILE__) . '/' );
}

require_once( dirname(__FILE__) . "/Core/System/class.core.php");

// Load it
$core = new Core();
$core->init();

?>