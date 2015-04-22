<?php

if (!defined('FUZESYSPATH')) {
	define('STARTTIME', microtime(true));
	define( 'FUZESYSPATH', dirname(__FILE__) . '/' ); 
}

// NotifierEvent, base event
// Framework
class Core {

	public $mods;
	private $loaded = false;
	public $register;

	## START/STOP
	public function init() {
		// Load basics
		ignore_user_abort(true);
		register_shutdown_function(array($this, "shutdown"));

		// Load core functionality
		$this->mods = new stdClass();
		$this->loadStartupFiles();

		$this->buildRegister();
		$this->mods->events->buildEventRegister();

		$event = $this->mods->events->fireEvent('coreStartEvent');
		if ($event->isCancelled()) {
			return true;
		}
	}

	public function loadStartupFiles() {
		if ($this->loaded)
			return;

		// Load core abstracts
		require_once(FUZESYSPATH . "/class.abstract.bus.php");
		require_once(FUZESYSPATH . "/class.abstract.event.php");
		require_once(FUZESYSPATH . "/class.abstract.module.php");
		require_once(FUZESYSPATH . "/class.abstract.model.php");
		require_once(FUZESYSPATH . "/class.abstract.eventPriority.php");

		// Load the core classes
		require_once(FUZESYSPATH . "/class.config.php");
		require_once(FUZESYSPATH . "/class.logger.php");
		require_once(FUZESYSPATH . "/class.models.php");
		require_once(FUZESYSPATH . "/class.layout.php");
		require_once(FUZESYSPATH . "/class.events.php");

		// Load them
        $this->mods->events 		= new Events 		($this);
		$this->mods->config	    	= new Config 		($this);
        $this->mods->logger      	= new Logger 		($this);
        $this->mods->models 		= new Models 		($this);
        $this->mods->layout 		= new Layout 		($this);

        $this->loaded = true;
	}

	public function shutdown() {
		$this->mods->events->fireEvent('coreShutdownEvent');
	}

	## MODLOADING
	public function loadMod($name, $version = null) {
		// Get class information
		$data = $this->loadModule($name, $version);

		// If it is an abstract class, create and StdClass
		if (empty($data)) {
			return $this->mods->{strtolower($name)} = new StdClass();
		}

		// Otherwise load the class
		$class_name = $data['className'];

		// Create the class object if not created yet
		if (!isset($this->mods->{strtolower($data['moduleLinkName'])})) {
			$CLASS = new $class_name($this);
			if (method_exists($CLASS, 'setModulePath')) {
				$CLASS->setModulePath($data['modulePath']);
			}
			if (method_exists($CLASS, 'setModuleLinkName')) {
				$CLASS->setModuleLinkName($data['moduleLinkName']);
			}
			if (method_exists($CLASS, 'setModuleName')) {
				$CLASS->setModuleName($data['moduleName']);
			}
			$CLASS->onLoad();

			return $this->mods->{strtolower($data['moduleLinkName'])} = &$CLASS;		
		} else {
			$c = &$this->mods->{strtolower($data['moduleLinkName'])};
			return $c;
		}	
	}

	private function loadModule($name, $version = null) {
		// The basic module path
		$path = FUZEPATH . "Modules/";

		// Chech if the requested module is set
		if (isset($this->register[$name])) {
			// Check if the config file is loaded
			if (!empty($this->register[$name])) {
				// Load the config file
				$cfg = (object) $this->register[$name];

				// Check if the module is enabled, otherwise abort
				if (isset($cfg->enabled)) {
					if (!$cfg->enabled) {
						// Module is disabled
						throw new Exception("Module '".$name."' is not enabled!", 1);
						return false;
					}
				}

				// Check if a specific version is requested
				if (isset($version)) {
					if (isset($cfg->versions)) {
						if (isset($cfg->versions[$version])) {
							$ncfg = (object) $cfg->versions[$version];
							foreach ($ncfg as $key => $value) {
								$cfg->$key = $value;
							}
						}
					}
				} 

				// Or load the main version
				$file = $cfg->directory . $cfg->module_file;

				// Load the dependencies before the module loads
				$deps = (isset($cfg->dependencies) ? $cfg->dependencies : array());
				for ($i=0; $i < count($deps); $i++) { 
					$this->loadMod($deps[$i]);
				}

				// Check if the file exists
				if (file_exists($file)) {
					// And load it
					require_once($file);
					$class_name = $cfg->module_class;
					$msg = "Loading Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name)) . "'";
					$msg .= (isset($cfg->version) ? " version:".$cfg->version : "");
					$msg .= (isset($cfg->author) ? " made by ".$cfg->author : "");
					$msg .= (isset($cfg->website) ? " from ".$cfg->website: "");
					$this->mods->logger->log($msg);
				} else {
					// Throw Exception if the file does not exist
					throw new Exception("Requested mod '".$name."' could not be loaded. Class file not found", 1);
					return false;							
				}
			} else {
				// Throw Exception if the module has an invalid config file
				throw new Exception("Requested mod '".$name."' could not be loaded. Invalid config", 1);
				return false;			
			}
		} else {
			// Throw Exception if the module is not defined
			throw new Exception("Requested mod '".$name."' was not found", 1);
			return false;			
		}

		// If it is an abstract module, return an StdClass for the memory address
		if (isset($cfg->abstract)) {
			if ($cfg->abstract) {
				$c = new stdClass();
				return array();
			}
		}

		return array('className' => $class_name,
			'modulePath' => $cfg->directory,
			'moduleLinkName' => $cfg->module_name,
			'moduleName' => $name);
	}

	public function buildRegister() {
		$this->mods->logger->newLevel("Loading Module Headers", 'Core');

		// Get all the module directories
		$dir = FUZEPATH . "Modules/";
		$mod_dirs = array();
		$mod_dirs = array_values(array_diff(scandir($dir), array('..', '.')));

		// Build the module register
		$register = array();
		for ($i=0; $i < count($mod_dirs); $i++) { 
			$mod_dir = $dir . $mod_dirs[$i] . "/";
			// If a moduleInfo.php exists, load it
			if (file_exists($mod_dir . "/moduleInfo.php")) {
        		$cfg = (object) require($mod_dir . "/moduleInfo.php");
        		$name = "";
        		$name .= (!empty($cfg->author) ? strtolower($cfg->author)."/" : "");
        		$name .= strtolower($cfg->module_name);

        		// Append directory
        		$cfg->directory = $mod_dir;
        		$register[$name] = (array) $cfg;
        		$this->mods->logger->log("Found module: '".$name."'");
			} else {
        		// Get the name
        		$name = $mod_dirs[$i];

        		// Build a default module config
        		$cfg = new stdClass();
        		$cfg->module_class = ucfirst($name);
        		$cfg->module_file = 'class.'.strtolower($name).".php";
        		$cfg->module_name = $name;
        		$cfg->dependencies = array();
        		$cfg->versions = array();
        		$cfg->directory = $mod_dir;
        		$register[$name] = (array)$cfg;
        		$this->mods->logger->log("Found module: '".$name."'");
			}
		}

		$this->register = $register;
		$this->mods->logger->stopLevel();
		
	}
}


?>