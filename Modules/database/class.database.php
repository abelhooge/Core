<?php

namespace Module\Database;
use \FuzeWorks\Module;
use \PDO;

class Main extends Module {

	/**
	 * The default database connection
	 * @access private
	 * @var PDO Class
	 */
	private $DBH;
	public $prefix;

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function onLoad() {
		$this->config->dbActive = true;
	}

	public function connect($config = null) {
		// If nothing is given, connect to database from the main config, otherwise use the served configuration
		if (is_null($config)) {
			$db = $this->mods->config->database;
		} else {
			$db = $config;
		}
		
		// Get the DSN for popular types of databases or a custom DSN
		switch (strtolower($db->type)) {
			case 'mysql':
				$dsn = "mysql:host=".$db->host.";";
				$dsn .= (!empty($db->database) ? "dbname=".$db->database.";" : "");
				break;
			case 'custom':
				$dsn = $db->dsn;
				break;
		}

		try {
			$this->mods->logger->logInfo("Connecting to '".$dsn."'", "Database");
			// And create the connection
			$this->DBH = new PDO($dsn, $db->username, $db->password, (isset($db->options) ? $db->options : null));
			$this->DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->mods->logger->logInfo("Connected to database", "Database");

			// And set the prefix
			$this->prefix = $db->prefix;
		} catch (Exception $e) {
			throw (new Exception('Could not connect to the database: "'. $e->getMessage() . '"'));
		}
	}

	public function getPrefix() {
		return $this->prefix;
	}

	public function is_active() {
        if($this->DBH === null){
        	return false;
        } else {
        	return true;
        }
	}

	public function __call($name, $params) {
		if ($this->is_active()) {
			return call_user_func_array(array($this->DBH, $name), $params);			
		} else {
			$this->connect();
			return call_user_func_array(array($this->DBH, $name), $params);	
		}
	}

	public function __get($name) {
		if ($this->is_active()) {
			return $this->DBH->$name;			
		} else {
			$this->connect();
			return $this->DBH->$name;		
		}		
	}

	public function __set($name, $value) {
		if ($this->is_active()) {
			$this->DBH->$name = $value;		
		} else {
			$this->connect();
			$this->DBH->$name = $value;	
		}		
	}
}


?>