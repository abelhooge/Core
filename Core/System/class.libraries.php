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
 * Libraries Class.
 *
 * FuzeWorks allows the user to use the built-in libraries as-is, and use it's functionality to get jobs done.
 * 
 * If a user wants to make their own libraries, they have, in general, 3 options:
 * 1. Create a completely new library
 * 2. Extend an existing library
 * 3. Replace an existing library
 * 
 * The first option is done by adding a new library to the Application/Libraries folder. If the library name is 'Example' then the
 * file should be 'Application/Libraries/Example.php' and the classname should be Example. Code can be added and it can be
 * loaded through Libraries->get('example');.
 * 
 * The second option allows the user to extend an existing core library. All functionality will be inherited in that situation. Let's take
 * the 'Zip' library as an example. The user needs to create a file in Application/Libraries. The name of the file and the class depend on
 * the configuration of FuzeWorks. The extended class needs to get a prefix, which is defined in config.main.php. By default, this is 'MY_'.
 * The user needs to create the file 'Application/Libraries/MY_Zip.php' with the classname MY_Zip. It can be loaded through Libraries->get('zip');.
 * 
 * The third option allows the user to replace a system library with their own. Doing so could potentially break systems, so be careful. 
 * If, for example we want to replace the Zip library, we need to create the file 'Application/Libraries/Zip.php' with the classname FW_Zip.
 * 'FW_' is the prefix for all FuzeWorks core libraries. And that's it. It can be loaded through Libraries->get('zip');.
 *
 * @todo 	  Implement events
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Libraries
{

	/**
	 * Factory object for interaction with FuzeWorks
	 * 
	 * @var Factory
	 */
	protected $factory;

	/**
	 * Array of all the paths where libraries can be found
	 * 
	 * @var array Library paths
	 */
	protected $libraryPaths = array('Core'.DS.'Libraries', 'Application'.DS.'Libraries');

	/**
	 * Array of all the loaded library objects
	 * 
	 * @var array All the loaded library objects, so they can be returned when reloading
	 */
	protected $libraries = array();

	/**
	 * Attach the Factory to this class
	 */
	public function __construct()
	{
		$this->factory = Factory::getInstance();
	}

	/**
	 * Library Loader
	 *
	 * Loads, instantiates and returns libraries.
	 *
	 * @param	string	$library		Library name
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @param	array	$directory 		Optional list of directories where the library can be found. Overrides default list
	 * @param   bool 	$newInstance 	Whether to return a new instance of the library or the same one
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	public function get($libraryName, array $parameters = null, array $directory = null, $newInstance = false) 
	{
		if (empty($libraryName)) 
		{
			throw new LibraryException("Could not load library. No name provided", 1);
		}

		return $this->loadLibrary($libraryName, $parameters, $directory, $newInstance);
	}

	/**
	 * Driver Library Loader
	 *
	 * Loads, instantiates and returns driver libraries.
	 *
	 * @param	string	$library		Driver Library name
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @param	array	$directory 		Optional list of directories where the library can be found. Overrides default list
	 * @param   bool 	$newInstance 	Whether to return a new instance of the library or the same one
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	public function getDriver($libraryName, array $parameters = null, array $directory = null, $newInstance = false)
	{
		if (empty($libraryName))
		{
			throw new LibraryException("Could not load driver. No name provided", 1);
		}

		// Load the driver class if it is not yet loaded
		if ( ! class_exists('FuzeWorks\FW_Driver_Library', false))
		{
			require_once('Core'.DS.'Libraries'.DS.'Driver.php');
		}

		// And then load and return the library
		return $this->loadLibrary($libraryName, $parameters, $directory, $newInstance);
	}

	/**
	 * Internal Library Loader
	 *
	 * Determines what type of library needs to be loaded
	 *
	 * @param	string	$library		Library name
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @param	array	$directory 		Optional list of directories where the library can be found. Overrides default list
	 * @param   bool 	$newInstance 	Whether to return a new instance of the library or the same one
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	protected function loadLibrary($libraryName, $parameters = null, array $directory = null, $newInstance = false) 
	{
		// First get the directories where the library can be located
		$directories = (is_null($directory) ? $this->libraryPaths : $directory);

		// Now figure out the className and subdir
		$class = trim($libraryName, '/');
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, ++$last_slash);

			// Get the filename from the path
			$class = substr($class, $last_slash);
		}
		else
		{
			$subdir = '';
		}

		$class = ucfirst($class);

		// Is the library a core library, then load a core library
		if (file_exists('Core'.DS.'Libraries'.DS.$subdir.$class.'.php'))
		{
			// Load base library
			return $this->loadCoreLibrary($class, $subdir, $parameters, $directories, $newInstance);
		}

		// Otherwise try and load an Application Library
		return $this->loadAppLibrary($class, $subdir, $parameters, $directories, $newInstance);
	}

	/**
	 * Core Library Loader
	 *
	 * Loads, instantiates and returns a core library.
	 *
	 * @param	string	$class			Classname
	 * @param   string 	$subdir 		Sub directory in which the final class can be found
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @param	array	$directory 		Optional list of directories where the library can be found. Overrides default list
	 * @param   bool 	$newInstance 	Whether to return a new instance of the library or the same one
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	protected function loadCoreLibrary($class, $subdir, array $parameters = null, array $directories, $newInstance = false) 
	{
		// First check if the input is correct
		if (!is_array($directories)) 
		{
			throw new LibraryException("Could not load module. \$directory variable was not an array", 1);
		}

		// Retrieve the subclass prefix
		$corePrefix = '\FuzeWorks\Library\FW_';
		$appPrefix = '\Application\Library\\' . Config::get('main')->application_prefix;
		$prefix = $corePrefix;

		// Perform a check to see if the library is already loaded
		if (class_exists($prefix.$class, false))
		{
			// Then check if an application extension also exists
			if (class_exists($appPrefix.$class, false))
			{
				$prefix = $appPrefix;
			}

			if (!isset($this->libraries[$prefix.$class]))
			{
				return $this->initLibrary($prefix.$class, $parameters);
			}

			// If required to do so, return the existing instance or load a new one
			if ($newInstance)
			{
				$this->factory->logger->log("Library '".$prefix.$class."' already loaded. Returning existing instance");
				return $this->libraries[$prefix.$class];	
			}

			$this->factory->logger->log("Library '".$prefix.$class."' already loaded. Returning new instance");
			return $this->initLibrary($prefix.$class, $parameters);
		}

		// Remove the core directory from the checklist
		if (in_array('Core'.DS.'Libraries', $directories))
		{
			array_shift($directories);
		}
		
		// First check the directories for the core library (the FW_ class)
		foreach ($directories as $directory) 
		{
			$file = $directory . DS . $subdir . $class . '.php';

			// Load if it exists
			if (file_exists($file))
			{
				// First base; if an app library is found with FW_, load that one. There can be no extensions
				include_once($file);
				if (class_exists($prefix.$class, false))
				{
					return $this->initLibrary($prefix.$class, $parameters);
				}
				else
				{
					// Otherwise log a message
					$this->factory->logger->logWarning("File ".$file." exists but does not declare $prefix$class");
				}
			}
		}

		// Second base; if no base class is found in the app folder, load it from the core folder
		include_once('Core'.DS.'Libraries'.DS.$subdir.$class.'.php');
		
		// Now let's check for extensions
		$subclass = $this->factory->config->getConfig('main')->application_prefix . $class;
		foreach ($directories as $directory) 
		{
			$file = $directory . DS . $subdir . $subclass . '.php';

			// Load if it exists
			if (file_exists($file))
			{
				include_once($file);
				if (class_exists($appPrefix.$class, false))
				{
					return $this->initLibrary($appPrefix.$class, $parameters);
				} 
				else 
				{
					$this->factory->logger->logWarning("File ".$file." exists but does not declare $prefix$class");
				}
			}
		}

		// Third and last base; just load the FW_ core class
		if (class_exists('\FuzeWorks\Library\FW_'.$class, false))
		{
			return $this->initLibrary('\FuzeWorks\Library\FW_'.$class, $parameters);
		}

		throw new LibraryException("Could not load library. File ".'Core'.DS.'Libraries'.DS.$subdir.$class.'.php'." exists but does not declare \FuzeWorks\Library\FW_$class", 1);
	}

	/**
	 * Application Library Loader
	 *
	 * Loads, instantiates and returns an application library.
	 * Could possibly extend a core library if requested.
	 *
	 * @param	string	$class			Classname
	 * @param   string 	$subdir 		Sub directory in which the final class can be found
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @param	array	$directory 		Optional list of directories where the library can be found. Overrides default list
	 * @param   bool 	$newInstance 	Whether to return a new instance of the library or the same one
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	protected function loadAppLibrary($class, $subdir, array $parameters = null, array $directories, $newInstance = false) 
	{
		// First check if the input is correct
		if (!is_array($directories)) 
		{
			throw new LibraryException("Could not load library. \$directory variable was not an array", 1);
		}

		// Search for the file
		foreach ($directories as $directory) 
		{
			// Skip the core directory
			if ($directory === 'Core'.DS.'Libraries')
			{
				continue;
			}

			// Determine the file
			$file = $directory . DS . $subdir . $class . '.php';
			$className = '\Application\Library\\'.$class;

			// Check if the file was already loaded
			if (class_exists($className, false)) 
			{
				// Return existing instance
				if (!isset($this->libraries[$className])) 
				{
					return $this->initLibrary($className, $parameters);
				}

				// If required to do so, return the existing instance or load a new one
				if ($newInstance)
				{
					$this->factory->logger->log("Library '".$className."' already loaded. Returning existing instance");
					return $this->libraries[$prefix.$class];	
				}

				$this->factory->logger->log("Library '".$className."' already loaded. Returning new instance");
				return $this->initLibrary($className, $parameters);
			}

			// Otherwise load the file first
			if (file_exists($file))
			{
				include_once($file);
				return $this->initLibrary($className, $parameters);
			}
		}

		// Maybe it's in a subdirectory with the same name as the class
		if ($subdir === '')
		{
			return $this->loadLibrary($class."/".$class, $parameters, $directories, $newInstance);
		}

		throw new LibraryException("Could not load library. Library was not found", 1);
	}

	/**
	 * Library Initializer
	 *
	 * Instantiates and returns a library.
	 * Determines whether to use the parameters array or a config file
	 *
	 * @param	string	$class			Classname
	 * @param	array	$params			Optional parameters to pass to the library class constructor
	 * @return	object
	 * @throws 	LibraryException
	 */ 
	protected function initLibrary($class, array $parameters = null) 
	{
		// First check to see if the library is already loaded
		if (!class_exists($class, false))
		{
			throw new LibraryException("Could not initiate library. Class not found", 1);
		}

		// Determine what parameters to use
		if (is_null($parameters) || empty($parameters))
		{
			try {
				$parameters = $this->factory->config->getConfig(strtolower($class))->toArray();
			} catch (ConfigException $e) {
				// No problem, just use an empty array instead
				$parameters = array();
			}
		}

		// Check if the adress is already reserved, if it is, we can presume that a new instance is requested.
		// Otherwise this code would not be reached
		if (isset($this->libraries[$class]))
		{
			$classObject = new $class($parameters);
			$this->factory->logger->log("Loaded new Library instance of: ".$class);
			return $classObject;
		} 
		else 
		{
			// Now load the class
			$this->libraries[$class] = new $class($parameters);
			$this->factory->logger->log("Loaded Library: ".$class);
			return $this->libraries[$class];
		}
	}

    /**
     * Add a path where libraries can be found
     * 
     * @param string $directory The directory
     * @return void
     */
	public function addLibraryPath($directory)
	{
		if (!in_array($directory, $this->libraryPaths))
		{
			$this->libraryPaths[] = $directory;
		}
	}

    /**
     * Remove a path where libraries can be found
     * 
     * @param string $directory The directory
     * @return void
     */  
	public function removeLibraryPath($directory)
	{
		if (($key = array_search($directory, $this->libraryPaths)) !== false) 
		{
		    unset($this->libraryPaths[$key]);
		}
	}

    /**
     * Get a list of all current libraryPaths
     * 
     * @return array Array of paths where libraries can be found
     */
	public function getLibraryPaths()
	{
		return $this->libraryPaths;
	}
}