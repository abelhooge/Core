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
 * Database loading class
 * 
 * Loads databases, forges and utilities in a standardized manner. 
 * 
 * @author  TechFuze <contact@techfuze.net>
 * @copyright (c) 2013 - 2014, TechFuze. (https://techfuze.net)
 * 
 */
class Database
{
    
    /**
     * The default database forge.
     * @var type FW_DB|null
     */
    protected static $defaultDB = null;
    
    /**
     * The default database forge.
     * @var type FW_DB_forge|null
     */
    protected static $defaultForge = null;
    
    /**
     * The default database utility.
     * @var type FW_DB_utility|null
     */
    protected static $defaultUtil = null;
    
    /**
     * Retrieve a database using a DSN or the default configuration.
     * 
     * If a string is provided like this: 'dbdriver://username:password@hostname/database',
     * the string will be interpreted and converted into a database connection parameter array.
     * 
     * If a string is provided with a name, like this: 'default' the 'default' connection from the
     * configuration file will be loaded. If no string is provided the default database will be loaded.
     * 
     * If the $newInstance is a true boolean, a new instance will be loaded instead of loading the 
     * default one. $newInstance will also make sure that the loaded database is not default one. 
     * This behaviour will be changed in the future. 
     * 
     * @todo Change $newInstance behaviour related to self::$defaultDB
     * 
     * If $queryBuilder = false is provided, the database will load without a queryBuilder. 
     * By default the queryBuilder will load.
     * 
     * @param string $parameters      
     * @param bool $newInstance
     * @param bool $queryBuilder
     * @return FW_DB
     */
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
    
    /**
     * Retrieves a database forge from the provided or default database.
     * 
     * If no database is provided, the default database will be used.
     * @todo Change $newInstance behaviour with default instances.
     * 
     * 
     * @param FW_DB $database
     * @param bool $newInstance
     * @return FW_DB_forge
     */
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
    
    /**
     * Retrieves a database utility from the provided or default database.
     * 
     * If no database is provided, the default database will be used.
     * @todo Change $newInstance behaviour with default instances.
     * 
     * 
     * @param FW_DB $database
     * @param bool $newInstance
     * @return FW_DB_utility
     */
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
}