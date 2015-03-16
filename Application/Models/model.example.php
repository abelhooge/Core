<?php

class Example extends Model{

    public function __construct(&$core){
        parent::__construct($core);

        $this->setType('techfuze/databasemodel', 'DatabaseModel');
        $this->fields   = array('id', 'key', 'value');
        $this->table    = 'example';
    }
}

?>