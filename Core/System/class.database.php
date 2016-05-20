<?php
/**
 * FuzeWorks.
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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

namespace FuzeWorks;
use FW_DB;

/**
 * Database Class.
 *
 * @todo Add documentation
 * @todo Implement Logger
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Database
{

	public static $defaultDB = null;
    public static $defaultForge = null;
    public static $defaultUtil = null;

    public static $databasePaths = array('Application'.DS.'Database', 'Core'.DS.'Database');

    public static function get($parameters = '', $newInstance = false, $queryBuilder = null) 
    {
        if (!$newInstance && is_object(self::$defaultDB) && ! empty(self::$defaultDB->conn_id))
        {
            return $reference = self::$defaultDB;
        }

        require_once ('Core'.DS.'Database'.DS.'DB.php');

        if ($newInstance)
        {
            return DB($parameters, $queryBuilder);
        }
        else
        {
            return self::$defaultDB = DB($parameters, $queryBuilder);
        }
    }

    public static function getForge($database = null, $newInstance = false)
    {
        // First check if we're talking about the default forge and that one is already set
        if (is_object($database) && $database === self::$defaultDB && is_object(self::$defaultForge))
        {
            return $reference = self::$defaultForge;
        }


        if ( ! is_object($database) OR ! ($database instanceof FW_DB))
        {
            isset(self::$defaultDB) OR self::get('', false);
            $database =& self::$defaultDB;
        }

        require_once ('Core'.DS.'Database'.DS.'DB_forge.php');
        require_once('Core'.DS.'Database'.DS.'drivers'.DS.$database->dbdriver.DS.$database->dbdriver.'_forge.php');

        if ( ! empty($database->subdriver))
        {
            $driver_path = 'Core'.DS.'Database'.DS.'drivers'.DS.$database->dbdriver.DS.'subdrivers'.DS.$database->dbdriver.'_'.$database->subdriver.'_forge.php';
            if (file_exists($driver_path))
            {
                require_once($driver_path);
                $class = 'FW_DB_'.$database->dbdriver.'_'.$database->subdriver.'_forge';
            }
        }
        else
        {
            $class = 'FW_DB_'.$database->dbdriver.'_forge';
        }

        // Create a new instance of set the default database
        if ($newInstance)
        {
            return new $class($database);
        }
        else 
        {
            return self::$defaultForge = new $class($database);
        }
    }

    public static function getUtil($database = null, $newInstance = false)
    {
        // First check if we're talking about the default util and that one is already set
        if (is_object($database) && $database === self::$defaultDB && is_object(self::$defaultUtil))
        {
            echo "CALLED";
            return $reference = self::$defaultUtil;
        }

        if ( ! is_object($database) OR ! ($database instanceof FW_DB))
        {
            isset(self::$defaultDB) OR self::get('', false);
            $database = & self::$defaultDB;
        }

        require_once ('Core'.DS.'Database'.DS.'DB_utility.php');
        require_once('Core'.DS.'Database'.DS.'drivers'.DS.$database->dbdriver.DS.$database->dbdriver.'_utility.php');
        $class = 'FW_DB_'.$database->dbdriver.'_utility';

        if ($newInstance)
        {
            return new $class($database);
        }      
        else
        {
            return self::$defaultUtil = new $class($database);
        }
    }

    public static function addDatabasePath($directory)
    {
        if (!in_array($directory, self::$databasePaths))
        {
            self::$databasePaths[] = $directory;
        }
    }

    public static function removeDatabasePath($directory)
    {
        if (($key = array_search($directory, self::$databasePaths)) !== false) 
        {
            unset(self::$databasePaths[$key]);
        }
    }

    public static function getDatabasePaths()
    {
        return self::$databasePaths;
    }

}
















