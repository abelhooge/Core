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

namespace FuzeWorks\Library;
use FuzeWorks\Config;
use FuzeWorks\Logger;
use FuzeWorks\LibraryException;
use FuzeWorks\Libraries;
use ReflectionObject;

/**
 * FuzeWorks Driver Library Class
 *
 * Converted from CodeIgniter.
 *
 * This class enables you to create "Driver" libraries that add runtime ability
 * to extend the capabilities of a class via additional driver objects
 *
 * @package		FuzeWorks
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link
 */
class FW_Driver_Library {

	/**
	 * Array of drivers that are available to use with the driver class
	 *
	 * @var array
	 */
	protected $valid_drivers = array();

	/**
	 * Name of the current class - usually the driver class
	 *
	 * @var string
	 */
	protected $lib_name;

	/**
	 * Get magic method
	 *
	 * The first time a child is used it won't exist, so we instantiate it
	 * subsequents calls will go straight to the proper child.
	 *
	 * @param	string	Child class name
	 * @return	object	Child class
	 */
	public function __get($child)
	{
		// Try to load the driver
		return $this->load_driver($child);
	}

	/**
	 * Load driver
	 *
	 * Separate load_driver call to support explicit driver load by library or user
	 *
	 * @param	string	Driver name (w/o parent prefix)
	 * @return	object	Child class
	 */
	public function load_driver($child)
	{
		// Get the subclass prefix
		$prefix = Config::get('main')->application_prefix;

		if ( ! isset($this->lib_name))
		{
			// Get library name without any prefix
			$this->lib_name = str_replace(array('FW_', $prefix), '{prefix}', get_class($this));
		}

		// Sanitize the library name
		$clean_class = str_replace(array('Application\Library\\','FuzeWorks\Library\\', '{prefix}'), '', $this->lib_name);

		// The child will be prefixed with the parent lib
		$child_name = $this->lib_name.'_'.$child;

		// See if requested child is a valid driver
		if ( ! in_array($child, $this->valid_drivers))
		{
			// The requested driver isn't valid!
			$msg = 'Invalid driver requested: '.$child_name;
			throw new LibraryException($msg, 1);
		}

		// Get package paths and filename case variations to search
		$paths = Libraries::getLibraryPaths();

		// Is there an extension?
		$class_name = str_replace('{prefix}', $prefix, $child_name);
		$found = class_exists($class_name, FALSE);
		if ( ! $found)
		{
			// Check for subclass file
			foreach ($paths as $path)
			{
				// Does the file exist?
				$file = $path.DS.$clean_class.DS.'drivers'.DS.$prefix.$clean_class.'_'.$child.'.php';
				if (file_exists($file))
				{
					// Yes - require base class from BASEPATH
					$basepath = 'Core'.DS.'Libraries'.DS.$clean_class.DS.'drivers'.DS.$clean_class.'_'.$child.'.php';
					if ( ! file_exists($basepath))
					{
						$msg = 'Unable to load the requested class: FW_'.$child_name;
						throw new LibraryException($msg, 1);
						
					}

					// Include both sources and mark found
					include_once($basepath);
					include_once($file);
					$found = TRUE;
					break;
				}
			}
		}

		// Do we need to search for the class?
		if ( ! $found)
		{
			// Use standard class name
			$class_name = str_replace('{prefix}', 'FW_', $child_name);
			if ( ! class_exists($class_name, FALSE))
			{
				// Check package paths
				foreach ($paths as $path)
				{
					// Does the file exist?
					$file = $path.DS.$clean_class.DS.'drivers'.DS.$clean_class.'_'.$child.'.php';
					if (file_exists($file))
					{
						// Include source
						include_once($file);
						break;
					}
				}
			}
		}

		// Did we finally find the class?
		if ( ! class_exists($class_name, FALSE))
		{
			if (class_exists($child_name, FALSE))
			{
				$class_name = $child_name;
			}
			else
			{
				$msg = 'Unable to load the requested driver: '.$class_name;
				throw new LibraryException($msg, 1);
			}
		}

		// Instantiate, decorate and add child
		$obj = new $class_name();
		$obj->decorate($this);
		$this->$child = $obj;
		return $this->$child;
	}

}

// --------------------------------------------------------------------------

/**
 * FuzeWorks Driver Class
 *
 * Converted from CodeIgniter 
 *
 * This class enables you to create drivers for a Library based on the Driver Library.
 * It handles the drivers' access to the parent library
 *
 * @package		FuzeWorks
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link
 */
class FW_Driver {

	/**
	 * Instance of the parent class
	 *
	 * @var object
	 */
	protected $_parent;

	/**
	 * List of methods in the parent class
	 *
	 * @var array
	 */
	protected $_methods = array();

	/**
	 * List of properties in the parent class
	 *
	 * @var array
	 */
	protected $_properties = array();

	/**
	 * Array of methods and properties for the parent class(es)
	 *
	 * @static
	 * @var	array
	 */
	protected static $_reflections = array();

	/**
	 * Decorate
	 *
	 * Decorates the child with the parent driver lib's methods and properties
	 *
	 * @param	object
	 * @return	void
	 */
	public function decorate($parent)
	{
		$this->_parent = $parent;

		// Lock down attributes to what is defined in the class
		// and speed up references in magic methods

		$class_name = get_class($parent);

		if ( ! isset(self::$_reflections[$class_name]))
		{
			$r = new ReflectionObject($parent);

			foreach ($r->getMethods() as $method)
			{
				if ($method->isPublic())
				{
					$this->_methods[] = $method->getName();
				}
			}

			foreach ($r->getProperties() as $prop)
			{
				if ($prop->isPublic())
				{
					$this->_properties[] = $prop->getName();
				}
			}

			self::$_reflections[$class_name] = array($this->_methods, $this->_properties);
		}
		else
		{
			list($this->_methods, $this->_properties) = self::$_reflections[$class_name];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * __call magic method
	 *
	 * Handles access to the parent driver library's methods
	 *
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __call($method, $args = array())
	{
		if (in_array($method, $this->_methods))
		{
			return call_user_func_array(array($this->_parent, $method), $args);
		}

		throw new LibraryException('No such method: '.$method.'()');
	}

	// --------------------------------------------------------------------

	/**
	 * __get magic method
	 *
	 * Handles reading of the parent driver library's properties
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function __get($var)
	{
		if (in_array($var, $this->_properties))
		{
			return $this->_parent->$var;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * __set magic method
	 *
	 * Handles writing to the parent driver library's properties
	 *
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __set($var, $val)
	{
		if (in_array($var, $this->_properties))
		{
			$this->_parent->$var = $val;
		}
	}

}
