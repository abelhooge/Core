<?php

namespace Module\Api;
use \FuzeWorks\Module;

class Main extends Module {

    public function onLoad() {
        require_once($this->getModulePath() . "/class.rest.php");
    }
}