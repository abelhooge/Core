<?php

namespace FuzeWorks;
use \Exception;

/**
 * Config Class
 * 
 * This class gives access to the config files. Can read and write .php files with an array in a file
 */
class Config extends Bus{

	/**
	 * Wether or not the database is active at the moment
	 * @access public
	 * @var Boolean true on active database
	 */
	public $dbActive = false;

	/**
	 * Class Constructor
	 * @access public
	 * @param FuzeWorks Core Reference
	 */
	public function __construct(&$core) {
		parent::__construct($core);
	}

	/**
	 * Get's called when the class get's loaded. Does nothing
	 * @access public
	 */
	public function onLoad() {}

	/**
	 * Reads a config file and returns it as object
	 * @access public
	 * @param String config file name
	 * @param String directory, default is Application/Config
	 * @throws \Exception on file not found
	 * @return StdObject of config
	 */
	public function loadConfigFile($name, $directory = null) {
		$dir = (isset($directory) ? $directory : FUZEPATH . "Application//config/");
		$file = $dir . 'config.' . strtolower($name).".php";

		if (file_exists($file)) {
			$DECODED = (object) require($file);
			return $DECODED;
		} else {
			$this->core->loadMod('techfuze/database');
			if ($this->dbActive) {
				// Fetch me a query of 5
				$prefix = $this->mods->database->getPrefix();
				$query = "SELECT * FROM ".$prefix."config WHERE `file` = ?";
				$binds = array($name);
		        try{
		            $sth = $this->mods->database->prepare($query);
		            $sth->execute($binds);
		        }catch (\PDOException $e){
		            throw new Exception('Could not execute SQL-query due PDO-exception '.$e->getMessage());
		        }

		        // Fetch results
		        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
		        $return = array();
		        for ($i=0; $i < count($result); $i++) { 
		        	$return[ $result[$i]['key'] ] = $result[$i]['value'];
		        }

		        // Return if found in DB
		        if (!empty($return)) {
		        	return (object) $return;
		        }

			}
			throw new Exception("Config file '".strtolower($name)."' was not found", 1);
			return false;
		}	
	}

	/**
	 * Change a value in the config, wherever this is saved
	 * @access public
	 * @param String filename
	 * @param String config key
	 * @param String/Array config value
	 * @param String directory, default is Application/Config
	 */
	public function set($name, $key, $value, $directory = null) {
		$dir = (isset($directory) ? $directory : FUZEPATH . "Application//config/");
		$file = $dir . 'config.' . strtolower($name).".php";
		if (file_exists($file)) {
			$DECODED = require($file);
			if (!is_array($DECODED)) {
				$DECODED = array();
			}
			if (is_null($value)) {
				unset($DECODED[$key]);
			} else {
				$DECODED[$key] = $value;
			}
			
			if (is_writable($file)) {
				$config = var_export($DECODED, true);
				file_put_contents($file, "<?php return $config ;");
			}
		} else {
			throw new Exception("Config file '".strtolower($name)."' was not found", 1);
			return false;
		}
	}

	/**
	 * Magic config getter
	 * @access public
	 * @param String config file name
	 * @return StdObject of config
	 */
	public function __get($name) {
		return $this->loadConfigFile($name);
	}
}


?>