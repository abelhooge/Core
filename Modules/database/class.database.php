<?php

use \FuzeWorks\Module;
use \Exception;
use \PDO;

class Database extends Module {

	private $DBH;
	public $prefix;

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function onLoad() {
		$this->connect($this->getSystemDbSettings());
		$this->config->dbActive = true;
	}

	public function connect($params = array()) {
		if (isset($params['type'])) {
			$type = $params['type'];
		} else {
			throw (new Exception("No database type given"));
		}

		if (isset($params['datb'])) {
			$database = $params['datb'];
		} else {
			throw (new Exception("No database given. Can not connect without database."));
		}

		if (isset($params['host'])) {
			$host = $params['host'];
		} else {
			throw (new Exception("No database host given. Can not connect without hostname."));
		}

		$username = $params['user'];
		$password = $params['pass'];
		$this->prefix = $params['prefix'];

		if (isset($params['options'])) {
			$options = $params['options'];
		} else {
			$options = null;
		}


		$DSN_FINAL = "";

		switch ($type) {
			case 'MYSQL':
				$DSN = "mysql:host=";
				$DSN2 = ";dbname=";
				
				// Check if charset is required
				if (isset($extraOptions)) {
					if (isset($extraOptions->charset)) {
						$DSN3 = ";charset=" . $extraOptions->charset;
					} else {
						$DSN3 = ";";
					}
				} else {
					$DSN3 = ";";
				}
				$DSN_FINAL = $DSN . $host . $DSN2 . $database . $DSN3;
				break;
			case 'sqlite':
				$DSN = 'sqlite:' . $host . ($database != '' ? ";dbname=" .$database : "");
				$DSN_FINAL = $DSN;
				break;
			default:
				throw (new Exception("Unknown database type given: '" . $type . "'"));
				break;
		}

		// Try and connect
        try{
            $this->mods->logger->logInfo("Connecting to '" . $DSN_FINAL. "'", "Database", __FILE__, __LINE__);
            $this->DBH = new \PDO($DSN_FINAL, $username, $password, $options);
            $this->DBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->mods->logger->logInfo("Connected!", "Database", __FILE__, __LINE__);
        }catch(\PDOException $e){
            throw (new Exception('Could not connect to the database: "'. $e->getMessage() . '"'));
        }
	}

	public function __call($name, $params) {
		if ($this->is_active()) {
			return call_user_func_array(array($this->DBH, $name), $params);			
		} else {
			$this->connect($this->getSystemDbSettings());
			return call_user_func_array(array($this->DBH, $name), $params);	
		}
	}

	public function __get($name) {
		if ($this->is_active()) {
			return $this->DBH->$name;			
		} else {
			$this->connect($this->getSystemDbSettings());
			return $this->DBH->$name;		
		}		
	}

	public function __set($name, $value) {
		if ($this->is_active()) {
			$this->DBH->$name = $value;		
		} else {
			$this->connect($this->getSystemDbSettings());
			$this->DBH->$name = $value;	
		}		
	}

	public function is_active() {
        if($this->DBH === null){
        	return false;
        } else {
        	return true;
        }
	}

	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Retrieve an array of the system DB settings. This is the configuration in the config file of FuzeWorks
	 * @access public
	 * @return DBSettings array
	 */
	public function getSystemDbSettings() {
		$dbsettings = array(
			'type' => $this->mods->config->database->type,
			'host' => $this->mods->config->database->host,
			'user' => $this->mods->config->database->username,
			'pass' => $this->mods->config->database->password,
			'datb' => $this->mods->config->database->database,
			'prefix' => $this->mods->config->database->prefix,
			);
		return $dbsettings;
	}
}


?>