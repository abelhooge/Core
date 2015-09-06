<?php
// Load the abstract

use \FuzeWorks\Config;
use \FuzeWorks\Core;

require_once "abstract.coreTestAbstract.php";
require_once( "Core/System/class.core.php");

ob_start();
Core::init();

// Disable debugger
$cfg = Config::get('error');
$cfg->debug = false;
$cfg->error_reporting = false;
$cfg->commit();

?>