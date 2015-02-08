<?php

class Example extends DatabaseModel{

    public function __construct(&$core){
        parent::__construct($core);

        $this->fields   = array('id', 'key', 'value');
        $this->table    = 'example';
    }
}

?>