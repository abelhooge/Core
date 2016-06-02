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
 * Factory Class.
 * 
 * The Factory class is the central point for class communication in FuzeWorks. 
 * When someone needs to load, for instance, the layout class, one has to do the following:
 * $factory = Factory::getInstance();
 * $layout = $factory->layout;
 * 
 * The Factory class allows the user to replace dependencies on the fly. It is possible for a class
 * to replace a dependency, like Logger, on the fly by calling the $factory->newInstance('Logger'); or the
 * $factory->setInstance('Logger', $object); This allows for creative ways to do dependency injection, or keep classes
 * separated. 
 * 
 * It is also possible to load a cloned instance of the Factory class, so that all properties are independant as well,
 * all to suit your very needs.
 * 
 * The Factory class is also extendible. This allows classes that extend Factory to access all it's properties. 
 * 
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Factory
{

	/**
	 * The Factory instance that is shared by default when calling Factory::getInstance();
	 * 
	 * @var FuzeWorks\Factory Default shared instance
	 */
	private static $sharedFactoryInstance;

	/**
	 * Whether to clone all Factory instances upon calling Factory::getInstance()
	 * 
	 * @var bool Clone all Factory instances.
	 */
	protected static $cloneInstances = false;

	/**
	 * Array of all the classes loaded by this specific instance of the Factory
	 * 
	 * @var array Array of all loaded classes in THIS Factory
	 */
	protected $instances = array();

	/**
	 * Factory instance constructor. Should only really be called once
	 * @return void
	 */
	public function __construct()
	{
		// If there is no sharedFactoryInstance, prepare it
		if (is_null(self::$sharedFactoryInstance))
		{
			self::$sharedFactoryInstance = $this;
	        $this->instances['Config'] = new Config();
	        $this->instances['Logger'] = new Logger();
	        $this->instances['Events'] = new Events();
	        $this->instances['Models'] = new Models();
	        $this->instances['Layout'] = new Layout();
	        $this->instances['Modules'] = new Modules();
	        $this->instances['Libraries'] = new Libraries();
	        $this->instances['Helpers'] = new Helpers();
	        $this->instances['Database'] = new Database();
	        $this->instances['Language'] = new Language();
	        $this->instances['Utf8'] = new Utf8();
	        $this->instances['URI'] = new URI();
	        $this->instances['Security'] = new Security();
	        $this->instances['Input'] = new Input();
	        $this->instances['Router'] = new Router();
	        $this->instances['Output'] = new Output();

	        return true;
		}

		// Otherwise, copy the existing instances
		$this->instances = self::getInstance()->getClassInstances();

		return true;
	}

	/**
	 * Get a new instance of the Factory class. 
	 * 
	 * @param bool $cloneInstance Whether to get a cloned instance (true) or exactly the same instance (false)
	 * @return FuzeWorks\Factory Factory Instance
	 */
	public static function getInstance($cloneInstance = false)
	{	
		if ($cloneInstance === true || self::$cloneInstances === true)
		{
			return clone self::$sharedFactoryInstance;
		}

		return self::$sharedFactoryInstance;
	}

	/**
	 * Enable cloning all Factory instances upon calling Factory::getInstance()
	 * 
	 * @return void
	 */
	public static function enableCloneInstances()
	{
		self::$cloneInstances = true;
	}

	/**
	 * Disable cloning all Factory instances upon calling Factory::getInstance()
	 * 
	 * @return void
	 */
	public static function disableCloneInstances()
	{
		self::$cloneInstances = false;
	}

	/**
	 * Return the instance array where all the instances are loaded
	 * 
	 * @return array Array of all loaded classes in THIS Factory
	 */
	public function getClassInstances() 
	{
		return $this->instances;
	}

	/**
	 * Create a new instance of one of the loaded classes.
	 * It reloads the class. It does NOT clone it. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @param string $namespace Optional namespace. Defaults to 'FuzeWorks\'
	 * @return FuzeWorks\Factory Factory Instance
	 */
	public function newInstance($className, $namespace = 'FuzeWorks\\')
	{
		// Determine the class to load
		$instanceName = ucfirst($className);
		$className = $namespace.$instanceName;

		if (!isset($this->instances[$instanceName]))
		{
			throw new FactoryException("Could not load new instance of '".$instanceName."'. Instance was not found.", 1);
		}
		elseif (!class_exists($className, false))
		{
			throw new FactoryException("Could not load new instance of '".$instanceName."'. Class not found.", 1);
		}

		// Remove the current instance
		unset($this->instances[$instanceName]);

		// And set the new one
		$this->instances[$instanceName] = new $className();

		// Return itself
		return $this;
	}

	/**
	 * Clone an instance of one of the loaded classes.
	 * It clones the class. It does NOT re-create it. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @return FuzeWorks\Factory Factory Instance
	 */
	public function cloneInstance($className)
	{
		// Determine the class to load
		$instanceName = ucfirst($className);

		if (!isset($this->instances[$instanceName]))
		{
			throw new FactoryException("Could not clone instance of '".$instanceName."'. Instance was not found.", 1);
		}

		// Clone the instance
		$this->instances[$instanceName] = clone $this->instances[$instanceName];

		// Return itself
		return $this;
	}

	/**
	 * Set an instance of one of the loaded classes with your own $object.
	 * Replace the existing class with one of your own.
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @param mixed  $object    Object to replace the class with
	 * @return FuzeWorks\Factory Factory Instance
	 */
	public function setInstance($className, $object)
	{
		// Determine the instance name
		$instanceName = ucfirst($className);

		// Unset and set
		unset($this->instances[$instanceName]);
		$this->instances[$instanceName] = $object;

		// Return itself
		return $this;
	}

	/**
	 * Remove an instance of one of the loaded classes. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @return FuzeWorks\Factory Factory Instance
	 */
	public function removeInstance($className)
	{
		// Determine the instance name
		$instanceName = ucfirst($className);

		if (!isset($this->instances[$instanceName]))
		{
			throw new FactoryException("Could not remove instance of '".$instanceName."'. Instance was not found.", 1);
		}

		// Unset
		unset($this->instances[$instanceName]);

		// Return itself
		return $this;
	}

	/**
	 * Get one of the loaded classes. Overloading method.
	 * 
	 * @param string $objectName Name of the class to get
	 * @return mixed The class requested
	 */
	public function __get($objectName)
	{
		if (isset($this->instances[ucfirst($objectName)]))
		{
			return $this->instances[ucfirst($objectName)];
		}

		return null;
	}

	/**
	 * Test if a class is set to the Factory instance
	 * 
	 * @param string $objectName Name of the class to get
	 * @return bool  Whether the class is set
	 */
	public function __isset($objectName)
	{
		return isset($this->instances[ucfirst($objectName)]);
	}

	/**
	 * Unset a class set to the Factory instance
	 * 
	 * @param string $objectName Name of the class to get
	 * @return void
	 */
	public function __unset($objectName)
	{
		unset($this->instances[ucfirst($objectName)]);
	}

	/* --------------------------------- Compatibility classes --------------------------------- */

	/**
	 * @deprecated
	 */
	public function getConfig()
	{
		return $this->instances['Config'];
	}

	/**
	 * @deprecated
	 */
	public function getCore()
	{
		return $this->instances['Core'];
	}

	/**
	 * @deprecated
	 */
	public function getDatabase()
	{
		return $this->instances['Database'];
	}

	/**
	 * @deprecated
	 */
	public function getEvents()
	{
		return $this->instances['Events'];
	}

	/**
	 * @deprecated
	 */
	public function getHelpers()
	{
		return $this->instances['Helpers'];
	}

	/**
	 * @deprecated
	 */
	public function getInput()
	{
		return $this->instances['Input'];
	}

	/**
	 * @deprecated
	 */
	public function getLanguage()
	{
		return $this->instances['Language'];
	}

	/**
	 * @deprecated
	 */
	public function getLayout()
	{
		return $this->instances['Layout'];
	}

	/**
	 * @deprecated
	 */
	public function getLibraries()
	{
		return $this->instances['Config'];
	}

	/**
	 * @deprecated
	 */
	public function getLogger()
	{
		return $this->instances['Logger'];
	}

	/**
	 * @deprecated
	 */
	public function getModels()
	{
		return $this->instances['Models'];
	}

		/**
	 * @deprecated
	 */
	public function getModules()
	{
		return $this->instances['Modules'];
	}

	/**
	 * @deprecated
	 */
	public function getOutput()
	{
		return $this->instances['Output'];
	}

	/**
	 * @deprecated
	 */
	public function getRouter()
	{
		return $this->instances['Router'];
	}

	/**
	 * @deprecated
	 */
	public function getSecurity()
	{
		return $this->instances['Security'];
	}

	/**
	 * @deprecated
	 */
	public function getUri()
	{
		return $this->instances['URI'];
	}

	/**
	 * @deprecated
	 */
	public function getUtf8()
	{
		return $this->instances['Utf8'];
	}


}
