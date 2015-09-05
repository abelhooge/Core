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

namespace Module\Database;
use \FuzeWorks\Module;
use \FuzeWorks\Config;
use \FuzeWorks\Logger;
use \PDO;
use \FuzeWorks\DatabaseException;

/**
 * Database Class
 *
 * This class is a wrapper for PDO.
 *
 * @package     net.techfuze.fuzeworks.database
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Main extends Module {

	/**
	 * The default database connection
	 * @access private
	 * @var PDO Class
	 */
	private $DBH;
	public $prefix;

	public function onLoad() {
		Config::$dbActive = true;
	}

	/**
	 * Connect to a database
	 * @access public
	 * @param StdObject Config, like the database config in Application/Config
	 */
	public function connect($config = null) {
		// If nothing is given, connect to database from the main config, otherwise use the served configuration
		if (is_null($config)) {
			$db = Config::get('database');
		} else {
			$db = $config;
		}

		if (empty($db->type) || empty($db->host)) {
			throw (new DatabaseException('Database is not configured!'));
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
			Logger::logInfo("Connecting to '".$dsn."'", "Database");
			// And create the connection
			$this->DBH = new PDO($dsn, $db->username, $db->password, (isset($db->options) ? $db->options : null));
			$this->DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			Logger::logInfo("Connected to database", "Database");

			// And set the prefix
			$this->prefix = $db->prefix;
		} catch (Exception $e) {
			throw (new DatabaseException('Could not connect to the database: "'. $e->getMessage() . '"'));
		}
	}

	public function getPrefix() {
		if (!$this->is_active()) {
			$this->connect();
		}
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