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

/**
 * Database Class.
 *
 * @todo Add documentation
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Database
{

	protected static $defaultDB = null;

    protected static $databasePaths = array('Application'.DS.'Database', 'Core'.DS.'Database');

    public static function get($parameters = '', $newInstance = true, $queryBuilder = null) 
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

    public static function getForge($database = null, $newInstance = null)
    {

    }

    public static function getUtil($database = null, $newInstance = null)
    {

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
















