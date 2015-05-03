<?php
// Include framework
require_once( dirname(__FILE__) . "/Core/System/class.core.php");

// Load it
$core = new \FuzeWorks\Core();
$core->init();
$core->mods->router->setPath( (isset($_GET['path']) ? $_GET['path'] : null)   );
$core->mods->router->route();

?>