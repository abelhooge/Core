<?php

namespace FuzeWorks;

class Interpret extends Model {

    public function __construct(&$core){
        parent::__construct($core);

        $this->setType('techfuze/databaseutils', 'Model');
        $this->table    = '';
    }

    public function table($name) {
    	$this->table = $name;

    }
}

?>