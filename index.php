<?php

require('load.php');

$core->loadMod('router');
$core->mods->router->setPath( (isset($_GET['path']) ? $_GET['path'] : null)   );
$core->mods->router->route();

?>