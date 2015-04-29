<?php
// Include framework
if (!defined('FUZEPATH')) {
	define( 'FUZEPATH', dirname(__FILE__) . '/' );
}

require_once( dirname(__FILE__) . "/Core/System/class.core.php");

// Load it
$core = new Core();
$core->init();
$core->loadMod('router');
$core->mods->router->setPath( (isset($_GET['path']) ? $_GET['path'] : null)   );
$core->mods->router->route();
$core->mods->router->loadController();

?>