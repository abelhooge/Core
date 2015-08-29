<?php
/**
 * FuzeWorks
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

namespace FuzeWorks;

/**
 * Config Class
 *
 * This class gives access to the config files. Can read and write .php files with an array in a file
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
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
		$dir = (isset($directory) ? $directory : "Application/Config/");
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
		$dir = (isset($directory) ? $directory : "Application/config/");
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