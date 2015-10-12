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

namespace FuzeWorks;
use \FuzeWorks\TemplateEngine\JSONEngine;
use \FuzeWorks\TemplateEngine\PHPEngine;
use \FuzeWorks\TemplateEngine\SmartyEngine;
use \FuzeWorks\TemplateEngine\TemplateEngine;

/**
 * Layout and Template Manager for FuzeWorks.
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Layout {

	/**
	 * The file to be loaded by the layout manager
	 * @var null|string
	 */
	public static $file = null;

	/**
	 * The directory of the file to be loaded by the layout manager
	 * @var null|string
	 */
	public static $directory = null;

	/**
	 * All assigned currently assigned to the template
	 * @var Array Associative Assigned Variable Array
	 */
	private static $assigned_variables = array();

	/**
	 * All engines that can be used for templates
	 * @var array of engines
	 */
	private static $engines = array();

	/**
	 * All file extensions that can be used and are bound to a template engine
	 * @var array of names of engines
	 */
	private static $file_extensions = array();

	/**
	 * Wether the template engines are already called.
	 * @var boolean True if loaded
	 */
	private static $engines_loaded = false;

	/**
	 * The currently selected template engine
	 * @var String name of engine
	 */
	private static $current_engine;

	/**
	 * Retrieve a template file using a string and a directory and immediatly echo it.
	 *
	 * What template file gets loaded depends on the template engine that is being used.
	 * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/view.dashboard.php file.
	 * You can also provide no particular engine, and the manager will decide what template to load.
	 * Remember that doing so will result in a LayoutException when multiple compatible files are found.
	 * @param  String $file      File to load
	 * @param  string $directory Directory to load it from
	 * @return void
	 * @throws LayoutException   On error
	 */
	public static function view($file, $directory = 'Application/Views') {
		echo self::get($file, $directory);
		return;
	}


	/**
	 * Retrieve a template file using a string and a directory.
	 *
	 * What template file gets loaded depends on the template engine that is being used.
	 * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/view.dashboard.php file.
	 * You can also provide no particular engine, and the manager will decide what template to load.
	 * Remember that doing so will result in a LayoutException when multiple compatible files are found.
	 * @param  String $file      File to load
	 * @param  string $directory Directory to load it from
	 * @return String            The output of the template
	 * @throws LayoutException   On error
	 */
	public static function get($file, $directory = 'Application/Views') {
		Logger::newLevel("Loading template file '".$file."' in '".$directory."'");

		// First load the template engines
		self::loadTemplateEngines();

		// First retrieve the filepath
		if (is_null(self::$current_engine)) {
			self::setFileFromString($file, $directory, array_keys(self::$file_extensions));
		} else {
			self::setFileFromString($file, $directory, self::$current_engine->getFileExtensions());
		}

		// Then assign some basic variables for the template
		self::$assigned_variables['viewDir'] = Config::get('main')->SITE_URL . preg_replace('#/+#','/', substr(self::$directory . "/", -strlen(self::$directory . "/") ) );
		self::$assigned_variables['siteURL'] = Config::get('main')->SITE_URL;
		self::$assigned_variables['siteLogo'] = Config::get('main')->SITE_LOGO_URL;
		self::$assigned_variables['serverName'] = Config::get('main')->SERVER_NAME;
		self::$assigned_variables['siteDomain'] = Config::get('main')->SITE_DOMAIN;
		self::$assigned_variables['adminMail'] = Config::get('main')->administrator_mail;
		self::$assigned_variables['contact'] = Config::get('contact')->toArray();

		// Select an engine if one is not already selected
		if (is_null(self::$current_engine)) {
			self::$current_engine = self::getEngineFromExtension( self::getExtensionFromFile(self::$file) );
		}

		self::$current_engine->setDirectory(self::$directory);

		// And run an Event to see what other parts have to say about it
        $event = Events::fireEvent('layoutLoadViewEvent', self::$file, self::$directory, self::$current_engine, self::$assigned_variables);

        // The event has been cancelled
        if($event->isCancelled()){

            return false;
        }

        // And refetch the data from the event
        self::$current_engine = $event->engine;
        self::$assigned_variables = $event->assigned_variables;

		Logger::stopLevel();

		// And finally run it
		return self::$current_engine->get($event->file, self::$assigned_variables);
	}

	/**
	 * Retrieve a Template Engine from a File Extension
	 * @param  String $extension File extention to look for
	 * @return Object            Template Engine
	 */
	public static function getEngineFromExtension($extension) {
		if (isset(self::$file_extensions[strtolower($extension)])) {
			return self::$engines[ self::$file_extensions[strtolower($extension)]];
		}

		throw new LayoutException("Could not get Template Engine. No engine has corresponding file extension", 1);
	}

	/**
	 * Retrieve the extension from a file string
	 * @param  String $fileString The path to the file
	 * @return String             Extension of the file
	 */
	public static function getExtensionFromFile($fileString) {
		return substr($fileString, strrpos($fileString, '.') + 1);
	}

	/**
	 * Converts a view string to a file using the directory and the used extensions.
	 *
	 * It will detect wether the file exists and choose a file according to the provided extensions
	 * @param  String $string     The string used by a controller. eg: 'dashboard/home'
	 * @param  String $directory  The directory to search in for the template
	 * @param  array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
	 * @return String             Filepath of the template
	 * @throws LayoutException    On error
	 */
	public static function getFileFromString($string, $directory, $extensions = array()) {
		$directory = preg_replace('#/+#','/',(!is_null($directory) ? $directory : "Application/Views") . "/");

		if (!file_exists($directory)) {
			throw new LayoutException("Could not get file. Directory does not exist", 1);
		}

		// Set the file name and location
		$viewSelector = explode('/', $string);
		if (count($viewSelector) == 1) {
			$viewSelector = 'view.'.$viewSelector[0];
		} else {
			// Get last file
			$file = end($viewSelector);

			// Reset to start
			reset($viewSelector);

			// Remove last value
			array_pop($viewSelector);

			$viewSelector[] = 'view.'.$file;

			// And create the final value
			$viewSelector = implode('/', $viewSelector);
		}

		// Then try and select a file
		$fileSelected = false;
		$selectedFile = null;
		foreach ($extensions as $extension) {
			$file = $directory . $viewSelector .".". strtolower($extension);
			$file = preg_replace('#/+#','/',$file);
			if (file_exists($file) && !$fileSelected) {
				$selectedFile = $file;
				$fileSelected = true;
				Logger::log("Found matching file: '". $file . "'");
			} elseif (file_exists($file) && $fileSelected) {
				throw new LayoutException("Could not select template. Multiple valid extensions detected. Can not choose.", 1);
			}
		}

		// And choose what to output
		if (!$fileSelected) {
			throw new LayoutException("Could not select template. No matching file found.");
		}

		return $selectedFile;
	}

	/**
	 * Converts a view string to a file using the directory and the used extensions.
	 * It also sets the file variable of this class.
	 *
	 * It will detect wether the file exists and choose a file according to the provided extensions
	 * @param  String $string     The string used by a controller. eg: 'dashboard/home'
	 * @param  String $directory  The directory to search in for the template
	 * @param  array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
	 * @return String             Filepath of the template
	 * @throws LayoutException    On error
	 */
	public static function setFileFromString($string, $directory, $extensions = array()) {
		self::$file = self::getFileFromString($string, $directory, $extensions);
		self::$directory = preg_replace('#/+#','/',(!is_null($directory) ? $directory : "Application/Views") . "/");
	}

	/**
	 * Get the current file to be loaded
	 * @return null|string Path to the file
	 */
	public static function getFile() {
		return self::$file;
	}

	/**
	 * Set the file to be loaded
	 * @param string $file Path to the file
	 */
	public static function setFile($file) {
		self::$file = $file;
	}

	/**
	 * Get the directory of the file to be loaded
	 * @return null|string Path to the directory
	 */
	public static function getDirectory() {
		return self::$directory;
	}

	/**
	 * Set the directory of the file to be loaded
	 * @param string $directory Path to the directory
	 */
	public static function setDirectory($directory) {
		self::$directory = $directory;
	}

	/**
	 * Assign a variable for the template
	 * @param  String $key   Key of the variable
	 * @param  Mixed  $value Value of the variable
	 */
	public static function assign($key, $value) {
		self::$assigned_variables[$key] = $value;
	}

	/**
	 * Set the title of the template
	 * @param String $title title of the template
	 */
	public static function setTitle($title) {
		self::$assigned_variables['title'] = $title;
	}

	/**
	 * Get the title of the template
	 * @return String title of the template
	 */
	public static function getTitle() {
		return self::$assigned_variables['title'];
	}

	/**
	 * Set the engine for the next layout
	 * @param String $name Name of the template engine
	 * @return boolean true on success
	 * @throws \FuzeWorks\LayoutException on error
	 */
	public static function setEngine($name) {
		self::loadTemplateEngines();
		if (isset(self::$engines[$name])) {
			self::$current_engine = self::$engines[$name];
			Logger::log('Set the Template Engine to ' . $name);
			return true;
		}
		throw new LayoutException("Could not set engine. Engine does not exist", 1);
	}

	/**
	 * Get a loaded template engine
	 * @param  String $name Name of the template engine
	 * @return Object       Object that implements \FuzeWorks\TemplateEngine
	 */
	public static function getEngine($name) {
		self::loadTemplateEngines();
		if (isset(self::$engines[$name])) {
			return self::$engines[$name];
		}
		throw new LayoutException("Could not return engine. Engine does not exist", 1);
	}

	/**
	 * Register a new template engine
	 * @param  Object $engineClass          Object that implements the \FuzeWorks\TemplateEngine
	 * @param  String $engineName           Name of the template engine
	 * @param  Array  $engineFileExtensions File extensions this template engine should be used for
	 * @return boolean                      true on success
	 * @throws \FuzeWorks\LayoutException   On error
	 */
	public static function registerEngine($engineClass, $engineName, $engineFileExtensions = array()) {
		// First check if the engine already exists
		if (isset(self::$engines[$engineName])) {
			throw new LayoutException("Could not register engine. Engine '".$engineName."' already registered", 1);
		}

		// Then check if the object is correct
		if ($engineClass instanceof TemplateEngine) {
			// Install it
			self::$engines[$engineName] = $engineClass;

			// Then define for what file extensions this Template Engine will work
			if (!is_array($engineFileExtensions)) {
				throw new LayoutException("Could not register engine. File extensions must be an array", 1);
			}

			// Then install them
			foreach ($engineFileExtensions as $extension) {
				if (isset(self::$file_extensions[strtolower($extension)])) {
					throw new LayoutException("Could not register engine. File extension already bound to engine", 1);
				}

				// And add it
				self::$file_extensions[strtolower($extension)] = $engineName;
			}

			// And log it
			Logger::log('Registered Template Engine: ' . $engineName);
			return true;
		}

		throw new LayoutException("Could not register engine. Engine must implement \FuzeWorks\TemplateEngine", 1);
	}

	/**
	 * Load the template engines by sending a layoutLoadEngineEvent
	 */
	public static function loadTemplateEngines() {
		if (!self::$engines_loaded) {
			Events::fireEvent('layoutLoadEngineEvent');
			// Load the engines provided in this file
			self::registerEngine(new PHPEngine(), 'PHP', array('php'));
			self::registerEngine(new SmartyEngine(), 'Smarty', array('tpl'));
			self::registerEngine(new JsonEngine(), 'JSON', array('json'));
			self::$engines_loaded = true;
		}
	}

	/**
	 * Calls a function in the current Template engine
	 * @param  String     $name   Name of the function to be called
	 * @param  Paramaters $params Parameters to be used
	 * @return Mixed              Function output
	 */
	public static function __callStatic($name, $params) {
		// First load the template engines
		self::loadTemplateEngines();

		if (!is_null(self::$current_engine)) {
			// Call user func array here
			return call_user_func_array(array(self::$current_engine, $name), $params);
		}
		throw new LayoutException("Could not access Engine. Engine not loaded", 1);
	}

	/**
	 * Resets the layout manager to its default state
	 */
	public static function reset() {
		if (!is_null(self::$current_engine)) {
			self::$current_engine->reset();
		}
		self::$current_engine = null;
		self::$assigned_variables = array();
		Logger::log("Reset the layout manager to its default state");
	}
}

namespace FuzeWorks\TemplateEngine;
use \FuzeWorks\LayoutException;
use \Smarty;

/**
 * Interface that all Template Engines must follow
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
interface TemplateEngine {
	/**
	 * Set the directory of the current template
	 * @param String $directory Template Directory
	 */
	public function setDirectory($directory);

	/**
	 * Handle and retrieve a template file
	 * @param  String $file               Template File
	 * @param  Array  $assigned_variables All the variables used in this view
	 * @return String                     Output of the template
	 */
	public function get($file, $assigned_variables);

	/**
	 * Retrieve the file extensions that this template engine uses
	 * @return Array   All used extensions. eg: array('php')
	 */
	public function getFileExtensions();

	/**
	 * Reset the template engine to its default state, so it can be used again clean.
	 */
	public function reset();
}

/**
 * Simple Template Engine that allows for PHP templates
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class PHPEngine implements TemplateEngine {

	/**
	 * The currently used directory by the template
	 * @var String
	 */
	protected $directory;

	/**
	 * All the currently assigned variables
	 * @var array
	 */
	protected $assigned_variables = array();

	public function setDirectory($directory) {
		$this->directory = $directory;
	}

	public function get($file, $assigned_variables) {
		// First set all the variables
		$this->assigned_variables = $assigned_variables;
		$vars = $this->assigned_variables;
		$directory = $this->directory;

		// Then run the file
		if (!is_null($file)) {
			ob_start();
			require($file);
			return ob_get_clean();
		}
	}

	public function getFileExtensions() {
		return array('php');
	}

	public function reset() {
		$this->directory = null;
		$this->assigned_variables = array();
	}
}

/**
 * Wrapper for the Smarty Template Engine
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class SmartyEngine implements TemplateEngine {

	/**
	 * The currently used directory by the template
	 * @var String
	 */
	protected $directory;

	/**
	 * All the currently assigned variables
	 * @var array
	 */
	protected $assigned_variables = array();

	/**
	 * Instance of the Smarty Template Engine
	 * @var \Smarty
	 */
	protected $smartyInstance;

	public function setDirectory($directory) {
		$this->directory = $directory;
	}

	public function get($file, $assigned_variables) {
		// First set all the variables
		$this->assigned_variables = $assigned_variables;

		// Load Smarty
		$this->loadSmarty();

		// Set the directory
		$this->smartyInstance->setTemplateDir($this->directory);

		// Then assign all variables
		foreach ($this->assigned_variables as $key => $value) {
			$this->smartyInstance->assign($key, $value);
		}

		// And finally, load the template
		return $this->smartyInstance->fetch($file);
	}

	/**
	 * Loads a Smarty instance if it is not already loaded
	 */
	private function loadSmarty() {
		if (is_null($this->smartyInstance)) {
			$this->smartyInstance = new Smarty();

			// Then prepare all variables
			$this->smartyInstance->setCompileDir("Core/Cache/Compile");
			$this->smartyInstance->setCacheDir("Core/Cache/");
		}
	}

	public function getFileExtensions() {
		return array('tpl');
	}

	public function reset() {
		$this->smartyInstance = null;
		$this->directory = null;
		$this->assigned_variables = array();
	}

	/**
	 * Retrieve a value from Smarty
	 * @param  String $name Variable name
	 * @return Mixed        Variable Value
	 * @throws \FuzeWorks\LayoutException on error
	 */
	public function __get($name) {
		// First load Smarty
		$this->loadSmarty();

		return $this->smartyInstance->$name;
	}

	/**
	 * Set a variable in Smarty
	 * @param String $name  Variable Name
	 * @param Mixed  $value Variable Value
	 * @throws \FuzeWorks\LayoutException on error
	 */
	public function __set($name, $value) {
		// First load Smarty
		$this->loadSmarty();

		$this->smartyInstance->$name = $value;
	}

	/**
	 * Calls a function in Smarty
	 * @param  String     $name   Name of the function to be called
	 * @param  Paramaters $params Parameters to be used
	 * @return Mixed              Function output
	 */
	public function __call($name, $params) {
		// First load Smarty
		$this->loadSmarty();
		return call_user_func_array(array($this->smartyInstance, $name), $params);
	}
}

/**
 * Template Engine that exports all assigned variables as JSON
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class JSONEngine implements TemplateEngine {

	/**
	 * All the currently assigned variables
	 * @var array
	 */
	protected $assigned_variables = array();

	/**
	 * Whether the JSON data should be parsed or left as is
	 * @var boolean true if to be parsed
	 */
	protected static $string_return = true;

	/**
	 * Whether the JSON data should be parsed or left as is
	 * @param true if to be parsed
	 */
	public static function returnAsString($boolean = true) {
		self::$string_return = $boolean;
	}

	public function setDirectory($directory) {
		return true;
	}

	public function get($file, $assigned_variables) {
		// First set all the variables
		$this->assigned_variables = $assigned_variables;

		// First set up the JSON array
		$json = array();

		// Look up if a file is provided
		if (!is_null($file)) {
			// Retrieve a file
			$string = file_get_contents($file);
			$json = json_decode($string, true);
		}

		// Then assign all variables
		$json['data'] = $this->assigned_variables;

		// And return it
		if (self::$string_return)
			return json_encode($json);

		return $json;
	}

	public function getFileExtensions() {
		return array('json');
	}

	public function reset() {
		$this->assigned_variables = array();
		$this->string_return = true;
	}

	public function test($param1, $param2, $param3) {
		return array($param1, $param2, $param3);
	}
}


