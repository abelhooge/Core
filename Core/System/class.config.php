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

use PDOException;
use FuzeWorks\ORM\ConfigFileORM;
use FuzeWorks\ORM\ConfigDatabaseORM;

/**
 * Config Class.
 *
 * This class gives access to the config files. It allows you to open configurations and edit them.
 * This class is extensible, but not yet from the outside
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Config
{
    /**
     * whether or not the database is active at the moment.
     *
     * @var bool true on active database
     */
    public static $dbActive = false;

    /**
     * All loaded Config files.
     *
     * @var array of ConfigORM
     */
    private static $cfg = array();

    /**
     * Loads a config file and returns it as an object.
     *
     * @param string config file name
     * @param string directory, default is Application/Config
     *
     * @throws \Exception on file not found
     *
     * @return \FuzeWorks\ORM\ConfigORM of config
     */
    public static function loadConfigFile($name, $directory = null)
    {
        $dir = (isset($directory) ? $directory : 'Application/Config/');
        $file = $dir.'config.'.strtolower($name).'.php';

        // If already loaded, return a reference to the ORM
        if (isset(self::$cfg[$name])) {
            return $cfg = self::$cfg[$name];
        }

        // Is this the real file?
        if (file_exists($file)) {
            // Is it just reference?
            return $cfg = self::$cfg[$name] = new ConfigFileORM($file);
        } else {
            // Caught in a datastream
            $module = Modules::get('core/database');
            // No escape from dbactive
            if (self::$dbActive) {
                // Open your stream
                $dborm = new ConfigDatabaseORM($module, $name);
                // Lookup for success
                if ($dborm->success) {
                    // And see
                    return $cfg = self::$cfg[$name] = $dborm;
                }
            }

            // I'm just a poor exception
            throw new ConfigException("Config file '".strtolower($name)."' was not found", 1);

            // I need no returnee
            return false;
        }
    }

    /**
     * Retrieves a config file from the Application folder
     *
     * @param string config file name
     *
     * @return \FuzeWorks\ORM\ConfigORM of config
     */
    public static function get($name)
    {
        return self::loadConfigFile($name);
    }
}

namespace FuzeWorks\ORM;

use Iterator;

/**
 * Abstract ConfigORM class.
 *
 * This class implements the iterator, so a config file can be accessed using foreach.
 * A file can also be returned using toArray(), so it will be converted to an array
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
abstract class ConfigORM implements Iterator
{
    /**
     * The original state of a config file. Can be reverted to using revert().
     *
     * @var StdObject Config file
     */
    protected $originalCfg;

    /**
     * The current state of a config file.
     *
     * @var StdObject Config file
     */
    protected $cfg;

    /**
     * Revert to the original conditions of the config file.
     */
    public function revert()
    {
        $this->cfg = $this->originalCfg;
    }

    /**
     * Checks if a requested key is set in the config file.
     *
     * @param string $name Parameter name
     *
     * @return bool true on isset, false on not
     */
    public function __isset($name)
    {
        return isset($this->cfg[$name]);
    }

    /**
     * Return a value from a config file.
     *
     * @param string $name Key of the requested entry
     *
     * @return mixed Value of the requested entry
     */
    public function __get($name)
    {
        return $this->cfg[$name];
    }

    /**
     * Sets an entry in the config file.
     *
     * @param string $name  Key of the entry
     * @param mixed  $value Value of the entry
     */
    public function __set($name, $value)
    {
        $this->cfg[$name] = $value;
    }

    /**
     * Unset a value in a config file.
     *
     * @param string Key of the entry
     */
    public function __unset($name)
    {
        unset($this->cfg[$name]);
    }

    /**
     * Iterator method.
     */
    public function rewind()
    {
        return reset($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function current()
    {
        return current($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function key()
    {
        return key($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function next()
    {
        return next($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function valid()
    {
        return key($this->cfg) !== null;
    }

    /**
     * Returns the config file as an array.
     *
     * @return array Config file
     */
    public function toArray()
    {
        return $this->cfg;
    }
}

/**
 * ORM class for config files in a database.
 *
 * Handles entries in the database of FuzeWorks and is able to dynamically update them when requested
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class ConfigDatabaseORM extends ConfigORM
{
    /**
     * The current connection to the database.
     *
     * @var \FuzeWorks\Database Database Connection
     */
    private $dbh;

    /**
     * whether the database connection has been successfully established.
     *
     * @var bool true on success
     */
    public $success = false;

    /**
     * The current filename.
     *
     * @var string filename
     */
    private $file;

    /**
     * Sets up the class and the connection to the database.
     *
     * @param \FuzeWorks\Database $db       The Database connection
     * @param string              $filename The current filename
     *
     * @throws ConfigException on fatal error
     */
    public function __construct($db, $filename)
    {
        $this->dbh = $db;
        $this->cfg = $this->openDb($filename);
        $this->originalCfg = $this->cfg;
        $this->file = $filename;
    }

    /**
     * Opens up a database connection with the requested filename.
     *
     * @param string $name Name of the file
     *
     * @return array Content of the file
     *
     * @throws ConfigException on fatal error
     */
    private function openDb($name)
    {
        $prefix = $this->dbh->getPrefix();
        try {
            $stmnt = $this->dbh->prepare('SELECT * FROM '.$prefix.'config WHERE `file` = ?');
            $stmnt->execute(array($name));
        } catch (PDOException $e) {
            throw new ConfigException('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }

        // Fetch results
        $result = $stmnt->fetchAll(\PDO::FETCH_ASSOC);
        $return = array();
        for ($i = 0; $i < count($result); ++$i) {
            $return[ $result[$i]['key'] ] = $result[$i]['value'];
        }

        // Return if found in DB
        if (!empty($return)) {
            $this->success = true;

            return (array) $return;
        }
    }

    /**
     * Write config updates to the database.
     *
     * @throws ConfigException on fatal error
     */
    private function writeDb()
    {
        // First arrays of all the fields that need to change
        $changed_fields = array();
        $removed_fields = array();
        $new_fields = array();

        // First check for changed and new feeds
        foreach ($this->cfg as $key => $value) {
            if (isset($this->originalCfg[$key])) {
                if ($this->originalCfg[$key] != $value) {
                    // Changed field
                    $changed_fields[$key] = $value;
                }
            } else {
                // New field
                $new_fields[$key] = $value;
            }
        }

        // Then check for removed fields
        foreach ($this->originalCfg as $key => $value) {
            if (!isset($this->cfg[$key])) {
                $removed_fields[$key] = $value;
            }
        }

        // First for the removed values
        $prefix = $this->dbh->getPrefix();
        try {
            $stmnt = $this->dbh->prepare('DELETE FROM '.$prefix.'config WHERE `file` = :file AND `key` = :key');
            foreach ($removed_fields as $key => $value) {
                $stmnt->execute(array('file' => $this->file, 'key' => $key));
            }
        } catch (PDOException $e) {
            throw new ConfigException('Could not change config due to PDOException: '.$e->getMessage(), 1);
        }

        // Then for the changed values
        try {
            $stmnt = $this->dbh->prepare('UPDATE '.$prefix.'config SET `value` = :value WHERE `file` = :file AND `key` = :key');
            foreach ($changed_fields as $key => $value) {
                $stmnt->execute(array('file' => $this->file, 'key' => $key, 'value' => $value));
            }
        } catch (PDOException $e) {
            throw new ConfigException('Could not change config due to PDOException: '.$e->getMessage(), 1);
        }

        // And finally for the new values
        try {
            $stmnt = $this->dbh->prepare('INSERT INTO '.$prefix.'config (`file`,`key`,`value`) VALUES (:file,:key,:value)');
            foreach ($new_fields as $key => $value) {
                $stmnt->execute(array('file' => $this->file, 'key' => $key, 'value' => $value));
            }
        } catch (PDOException $e) {
            throw new ConfigException('Could not change config due to PDOException: '.$e->getMessage(), 1);
        }
    }

    /**
     * Write updates of the config file to the database.
     *
     * @throws ConfigException on fatal error
     */
    public function commit()
    {
        $this->writeDb();
    }
}

/**
 * ORM class for config files in PHP files.
 *
 * Handles entries in the config directory of FuzeWorks and is able to dynamically update them when requested
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class ConfigFileORM extends ConfigORM
{
    /**
     * The current filename.
     *
     * @var string filename
     */
    private $file;

    /**
     * Sets up the class and the connection to the PHP file.
     *
     * @param string $filename The current filename
     *
     * @throws ConfigException on fatal error
     */
    public function __construct($file)
    {
        if (file_exists($file)) {
            $this->file = $file;
            $this->openFile($file);
            $this->originalCfg = $this->cfg;
        } else {
            throw new ConfigException('Could not load config file. Config file does not exist', 1);
        }
    }

    /**
     * Opens the file and returns the data.
     *
     * @param string $file Name of the config file
     *
     * @return array Content of the file
     */
    private function openFile($file)
    {
        $this->cfg = (array) include $file;
    }

    /**
     * Updates the config file and writes it to the database.
     *
     * @throws ConfigException on fatal error
     */
    private function writeFile()
    {
        // Implement writing here
        if (is_writable($this->file)) {
            $config = var_export($this->cfg, true);
            file_put_contents($this->file, "<?php return $config ;");

            return true;
        }
        throw new ConfigException("Could not write config file. $file is not writable", 1);
    }

    /**
     * Updates the config file and writes it.
     *
     * @throws ConfigException on fatal error
     */
    public function commit()
    {
        $this->writeFile();
    }
}
