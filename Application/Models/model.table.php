<?php
namespace Model;
use \FuzeWorks\Model;

class Table extends Model{

    public function __construct(){

    	$this->setType('core/databaseutils', 'Model');
        $this->fields   = '*';
        $this->table    = 'table';
    }
}