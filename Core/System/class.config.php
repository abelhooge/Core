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
use FuzeWorks\ConfigORM\ConfigORMAbstract;
use FuzeWorks\ConfigORM\ConfigFileORM;

/**
 * Config Class.
 *
 * This class gives access to the config files. It allows you to open configurations and edit them.
 * This class is extensible, but not yet from the outside
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @todo      Fix the whole thing, it works, terribly
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
     * All registered ORMs
     * 
     * @var array of ORM names and objects
     */
    private $ORMs = array();


    /**
     * The config tree, this is where all configs are stored whatever ORM they come from.
     * 
     * @var array
     */
    private $tree = array();

    private $scope = 'Application/Config';

    private static $factory;

    public function __construct()
    {
        $this->loadDefaultORM();
    }

    public function getConfig($identifier, $scope = null, $appendRoot = true) 
    {
        $scope = (is_null($scope) ? $this->scope : $scope);
        $object = $this->getEndpointFromString($identifier, $appendRoot);

        // Now check if we have an ORM or a result object
        if ($object->isLoaded() === false)
        {
            $object->setScope($scope);
            $object->retrieve();
        }  

        return $object;
    }

    public function getEndpointFromString($string, $appendRoot = true)
    {
        // If the root directory needs to be appended, do so
        if ($appendRoot === true)
        {
            $string = './' . str_replace(array('./'), '', $string);
        }

        // First sanitize the string
        $string = preg_replace('#/+#', '/', $string);

        // Check if the string is a valid filesystem string (which is the criteria for this system)
        if (strpbrk($string, "\\/?%*:|\"<>") === TRUE)
        {
            throw new ConfigException('Could not get Config. Invalid string', 1);
        }

        // Prepare all variables for the search of the endpoint
        $endpoints = explode('/', $string);
        $ORM = null;
        $ResultObject = null;
        $tree = $this->tree;
        $ORMTree = $endpoints;

        // Cycle through the tree
        for ($i=0; $i < count($endpoints); $i++) 
        {  
            // If this position in the tree is found, check all posibilities
            $key = $endpoints[$i];
            if (isset($tree[$key]))
            {
                // Is an ORM found? Register it as the last ORM for now
                if (isset($tree[$key]['ORM']) && is_object($tree[$key]['ORM']))
                {
                    $ORM = $tree[$key]['ORM'];
                    if ($ORM->isLoaded() === false)
                    {
                        $ORM = clone $ORM;
                    }
                    array_shift($ORMTree);
                }
            }
            else
            {
                // Not found, use last found ORM
                break;
            }

            // Update the working tree
            $tree = $tree[$key];
        }

        // If absolutely nothing is found, throw an exception
        if (is_null($ORM))
        {
            throw new ConfigException("Could not get Config. '".$string."'. No Object Mapper found.");
        }

        // And add the ORM to the final point
        $last = end($endpoints);
        reset($endpoints);
        $endpoints[$last]['ORM'] = $ORM;
        $this->tree = array_merge($this->tree, $endpoints);

        // Determine the object to be loaded
        if ($ORM->isLoaded() === false)
        {
            $ORM->setTree($ORMTree);            
        }

        return $ORM;
    }

    public function registerEndpoint($endpointString, $ORMName, $appendRoot = true)
    {
        if (!isset($this->ORMs[$ORMName]))
        {
            throw new ConfigException("Could not register endpoint. ORM '".$ORMName."' is not known (not registered?)", 1);
        }

        // If the root directory needs to be appended, do so
        if ($appendRoot === true)
        {
            $endpointString = './' . str_replace(array('./'), '', $endpointString);
        }

        // First sanitize the string
        $endpointString = preg_replace('#/+#', '/', $endpointString);

        // Check if the string is a valid filesystem string (which is the criteria for this system)
        if (strpbrk($endpointString, "\\/?%*:|\"<>") === TRUE)
        {
            throw new ConfigException('Could not register endpoint. Invalid string', 1);
        }

        // Prepare all variables for the search of the endpoint
        $endpoints = explode('/', $endpointString);

        // Check for illegal tags
        if (in_array('ORM', $endpoints) || in_array('ResultObject', $endpoints))
        {
            throw new ConfigException('Could not register endpoint. Endpoint can not contain \'ORM\' or \'ResultObject\' values.');
        }

        // And add the ORM to the final point
        $last = end($endpoints);
        reset($endpoints);

        $endpoints[$last]['ORM'] = clone $this->ORMs[$ORMName];

        // And finally merge the tree
        $this->tree = array_merge($this->tree, $endpoints);
    }

    public function registerORM(ConfigORMAbstract $ORMObject, $ORMName, $appendRoot = true)
    {
        // First check if the ORM already exists
        if (isset($this->ORMs[$ORMName]))
        {
            throw new ConfigException("Could not register ORM. ORM '".$ORMName."' already registered", 1);
        }

        // Then validate the ORM
        if ($ORMObject instanceof ConfigORMAbstract)
        {
            $this->ORMs[$ORMName] = $ORMObject;
        }

        // And log it

        return true;
    }

    /**
     * Loads a config file and returns it as an object.
     *
     * @param string config file name
     * @param string directory, default is Application/Config
     *
     * @throws FuzeWorks\ConfigException on file not found
     *
     * @return FuzeWorks\ORM\ConfigORM of config
     */
    public static function loadConfigFile($name, $scope = null)
    {
        $dir = (isset($scope) ? $scope : 'Application/Config/');
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
            /*$module = Modules::get('core/database');
            // No escape from dbactive
            if (self::$dbActive) {
                // Open your stream
                $dborm = new ConfigDatabaseORM($module, $name);
                // Lookup for success
                if ($dborm->success) {
                    // And see
                    return $cfg = self::$cfg[$name] = $dborm;
                }
            }*/

            // I'm just a poor exception
            throw new ConfigException("Config file '".strtolower($name)."' was not found", 1);
        }
    }

    public function loadDefaultORM()
    {
        $this->registerORM(new ConfigFileORM(), 'File');
        $this->registerEndpoint('.', 'File', false);
    }

    public function reset()
    {
        // Reset the variables
        $this->tree = array();
        $this->ORMs = array();
    }

    public function getDirectory()
    {
        return $this->scope;
    }

    public function setDirectory($scope)
    {
        // Check if the string is a valid filesystem string (which is the criteria for this system)
        if (strpbrk($scope, "\\/?%*:|\"<>") === TRUE)
        {
            throw new ConfigException('Could not set directory. Invalid string', 1);
        }

        $this->scope = $scope;
    }

    /**
     * Retrieves a config file from the Application folder
     *
     * @param string config file name
     *
     * @return FuzeWorks\ORM\ConfigORM of config
     */
    public static function get($name)
    {
        if (!is_object(self::$factory))
        {
            self::$factory = Factory::getInstance();
        }
        $config = self::$factory->getConfig();
        return $config->getConfig($name);
    }
}

namespace FuzeWorks\ConfigORM;

use Iterator;
use FuzeWorks\ConfigException;

interface ConfigORMInterface {
    public function setTree($tree);
    public function commit();
    public function setScope($scope);
    public function retrieve();

    /**
     * Whether the ConfigORM is already loaded.
     * 
     * If true, the config should not be reloaded
     */
    public function isLoaded();
}

/**
 * Abstract ConfigORM class.
 *
 * This class implements the iterator, so a config file can be accessed using foreach.
 * A file can also be returned using toArray(), so it will be converted to an array
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
abstract class ConfigORMAbstract implements Iterator
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
 * ORM class for config files in PHP files.
 *
 * Handles entries in the config directory of FuzeWorks and is able to dynamically update them when requested
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class ConfigFileORM extends ConfigORMAbstract implements ConfigORMInterface
{
    /**
     * The current filename.
     *
     * @var string filename
     */
    private $file;

    private $loaded = false;

    private $tree = array();

    private $directory = 'Application/Config';

    /**
     * Sets up the class and the connection to the PHP file.
     *
     * @param string $filename The current filename
     *
     * @throws ConfigException on fatal error
     */
    public function __construct($file = null)
    {
        if (is_null($file))
        {
            return;
        }
        elseif (file_exists($file)) {
            $this->file = $file;
            $this->openFile($file);
            $this->originalCfg = $this->cfg;
        } else {
            throw new ConfigException('Could not load config file. Config file does not exist', 1);
        }
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    public function setTree($tree)
    {
        $this->tree = $tree;
    }

    public function retrieve()
    {
        // Retrieve the value from the tree
        $file = end($this->tree);
        $fileKey = key($this->tree);

        // Replace it with the new one
        $actualFile = 'config.'.$file.'.php';
        unset($this->tree[$fileKey]);
        $this->tree[] = $actualFile;

        // Create the filestring and open it
        $this->file = preg_replace('#/+#', '/', $this->directory . '/' . implode('/', $this->tree));
        $this->openFile($this->file);
        $this->originalCfg = $this->file;

        $this->loaded = true;
    }

    public function setScope($scope)
    {
        $this->directory = $scope;
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
