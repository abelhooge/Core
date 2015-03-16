<?php

class Interpret extends Model {

    public function __construct(&$core){
        parent::__construct($core);

        $this->setType('techfuze/databasemodel', 'DatabaseModel');
        $this->fields   = array();
        $this->table    = '';
    }

    public function table($name) {
    	$this->table = $name;

    	// Get tables
    	$sth = $this->mods->database->prepare("DESCRIBE " . $this->table);
    	$sth->execute();
		$table_fields = $sth->fetchAll(\PDO::FETCH_COLUMN);

		// Append to model
		$this->fields = $table_fields;
    }

    public function toPHP() {
        $values = array();
        foreach ($this->fields as $key => $value) {
            $values[] = '"'.$value.'"';
        }
        $values = implode(', ', $values);
        $text = 'VALUES: array('.$values.')
        TABLE: '.$this->table;
        echo $text;
    }
}

?>