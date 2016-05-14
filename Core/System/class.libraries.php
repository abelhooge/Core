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
 * @todo add documentation
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Libraries
{

	protected static $libraryPaths = array('Core'.DS.'Libraries', 'Application'.DS.'Libraries');

	protected static $libraries = array();

	public static function get($libraryName, array $parameters = null, $directory = null) 
	{
		if (empty($libraryName)) 
		{
			return Logger::logError("Could not load library. No name provided");
		}

		return self::loadLibrary($libraryName, $parameters, $directory);
	}

	private static function loadLibrary($libraryName, $parameters = null, $directory = null) 
	{
		// First get the directories where the library can be located
		$directories = (is_null($directory) ? self::$libraryPaths : array($directory));

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
			return self::loadCoreLibrary($class, $subdir, $parameters, $directories);
		}

		// Otherwise try and load an Application Library
		return self::loadAppLibrary($class, $subdir, $parameters, $directories);
	}

	private static function loadCoreLibrary($class, $subdir, $parameters, array $directories) 
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

			if (!isset(self::$libraries[$prefix.$class]))
			{
				return self::initLibrary($prefix.$class, $parameters);
			}

			Logger::log("Library '".$prefix.$class."' already loaded. Returning existing instance");
			return self::$libraries[$prefix.$class];
		}

		// Remove the core directory from the checklist
		array_shift($directories);

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
					return self::initLibrary($prefix.$class, $parameters);
				}
				else
				{
					// Otherwise log a message
					Logger::logWarning("File ".$file." exists but does not declare $prefix$class");
				}
			}
		}

		// Second base; if no base class is found in the app folder, load it from the core folder
		include_once('Core'.DS.'Libraries'.DS.$subdir.$class.'.php');
		
		// Now let's check for extensions
		$subclass = Config::get('main')->application_prefix . $class;
		foreach ($directories as $directory) 
		{
			$file = $directory . DS . $subdir . $subclass . '.php';

			// Load if it exists
			if (file_exists($file))
			{
				include_once($file);
				if (class_exists($appPrefix.$class, false))
				{
					return self::initLibrary($appPrefix.$class, $parameters);
				} 
				else 
				{
					Logger::logWarning("File ".$file." exists but does not declare $prefix$class");
				}
			}
		}

		// Third and last base; just load the FW_ core class
		if (class_exists('\FuzeWorks\Library\FW_'.$class, false))
		{
			return self::initLibrary('\FuzeWorks\Library\FW_'.$class, $parameters);
		}

		throw new Exception("Could not load library. File ".'Core'.DS.'Libraries'.DS.$subdir.$class.'.php'." exists but does not declare \FuzeWorks\Library\FW_$class", 1);
	}

	private static function loadAppLibrary($class, $subdir, $parameters, array $directories) 
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
			$class = '\Application\Library\\'.$class;

			// Check if the file was already loaded
			if (class_exists($class, false)) 
			{
				// Return existing instance
				if (!isset(self::$libraries[$class])) 
				{
					return self::initLibrary($class, $parameters);
				}

				Logger::log("Library '".$class."' already loaded. Returning existing instance");
				return self::$libraries[$class];
			}

			// Otherwise load the file first
			if (file_exists($file))
			{
				include_once($file);
				return self::initLibrary($class, $parameters);
			}

			// Maybe it's in a subdirectory with the same name as the class
			if ($subdir === '')
			{
				return self::loadLibrary($class."/".$class, $parameters);
			}
		}

		throw new LibraryException("Could not load library. Library was not found", 1);
	}

	private static function initLibrary($class, $parameters) 
	{
		// First check to see if the library is already loaded
		if (!class_exists($class, false))
		{
			throw new LibraryException("Could not initiate library. Class not found", 1);
		}

		// Check if the adress is already reserved
		if (isset(self::$libraries[$class]))
		{
			return Logger::logWarning("Library is already loaded. Aborting");
		}

		// Now load the class
		$parameters = (is_null($parameters) ? array() : $parameters);
		self::$libraries[$class] = new $class($parameters);
		Logger::log("Loaded Library: ".$class);
		return $c = self::$libraries[$class];
	}

	public static function addLibraryPath($directory)
	{
		if (!in_array($directory, self::$libraryPaths))
		{
			self::$libraryPaths[] = $directory;
		}
	}

	public static function removeLibraryPath($directory)
	{
		if (($key = array_search($directory, self::$libraryPaths)) !== false) 
		{
		    unset(self::$libraryPaths[$key]);
		}
	}

	public static function getLibraryPaths()
	{
		return self::$libraryPaths;
	}
}