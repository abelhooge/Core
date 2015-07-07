<?php

namespace Module\Api;
use \FuzeWorks\Module;

class Main extends Module {

    public function __construct(&$core){
        parent::__construct($core);
    }

    public function onLoad() {
        require_once($this->getModulePath() . "/class.rest.php");
    }
}