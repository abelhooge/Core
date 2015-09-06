<?php

namespace Model;
use \FuzeWorks\Model;

class Example extends Model{

    public function __construct(&$core){
        parent::__construct($core);

        $this->setType('core/databaseutils', 'Model');
        $this->table    = 'example';
    }
}

?>