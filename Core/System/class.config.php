<?php

class Config extends Bus{

	public $dbActive = false;

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function onLoad() {}

	public function loadConfigFile($name, $directory = null) {
		$dir = (isset($directory) ? $directory : FUZEPATH . "Application//config/");
		$file = $dir . 'config.' . strtolower($name).".php";
		$file2 = $dir . 'config.' . strtolower($name).".enc.cfg";

		if (file_exists($file)) {
			$DECODED = (object) require($file);
			return $DECODED;
		} elseif (file_exists($file2)) {
			$data = file_get_contents($file2);
			return json_decode($data);
		} else {
			$this->core->loadMod('database');
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

	public function __get($name) {
		return $this->loadConfigFile($name);
	}
}


?>