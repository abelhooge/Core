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
	private $register;

	## START/STOP
	public function init() {
		// Load basics
		ignore_user_abort(true);
		register_shutdown_function(array($this, "shutdown"));

		// Load core functionality
		$this->mods = new stdClass();
		$this->loadStartupFiles();

		$this->mods->events->fireEvent('coreStartEvent');
		// Mod register exists, check if expired
		if ( ( date('U') - $this->mods->config->main->registers_last_update) > $this->mods->config->main->registers_update_interval) {
			$this->mods->logger->log("Registers have expired. Updating...", 'Core');
			$this->buildModRegister();
			$this->mods->events->buildEventRegister();
		}
	}

	public function loadStartupFiles() {
		if ($this->loaded)
			return;

		// Load core abstracts
		require_once(FUZESYSPATH . "/class.abstract.bus.php");
		require_once(FUZESYSPATH . "/class.abstract.event.php");
		require_once(FUZESYSPATH . "/class.abstract.module.php");
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
		$CLASS = $this->loadModule($name);
		if (!isset($this->mods->{strtolower($CLASS[1])})) {
			return $this->mods->{strtolower($CLASS[1])}  = &$CLASS[0];
		}	
	}

	public function getMod($name, $version = null) {
		$CLASS = $this->loadModule($name);
		return $CLASS[0];
	}

	private function loadModule($name, $version = null) {
		// Load the register if not loaded yet
		if (!isset($this->mods->config->modregister->register)) {
			$this->buildModRegister();
		} else {

			$this->register = $this->mods->config->modregister->register;
		}

		// The basic module path
		$path = FUZEPATH . "/Core/Mods/";

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

		// Create class object
		$CLASS = new $class_name($this);
		if (method_exists($CLASS, 'setModulePath')) {
			$CLASS->setModulePath($cfg->directory);
		}
		if (method_exists($CLASS, 'setModuleLinkName')) {
			$CLASS->setModuleLinkName($cfg->module_name);
		}
		if (method_exists($CLASS, 'setModuleName')) {
			$CLASS->setModuleName($name);
		}
		$CLASS->onLoad();
		return array($CLASS, $cfg->module_name);
	}

	public function buildModRegister() {
        $this->mods->logger->newLevel("Building Mod Register", 'Core');
        $dir = FUZEPATH . "Core/Mods/";
        $mods = array_values(array_diff(scandir($dir), array('..', '.')));
        $register = array();
        for ($i=0; $i < count($mods); $i++) { 
        	$mod_dir = $dir . $mods[$i] . "/";
        	if (file_exists($mod_dir . "/moduleInfo.php")) {
        		$cfg = (object) require($mod_dir . "/moduleInfo.php");
        		$name = "";
        		$name .= (!empty($cfg->author) ? strtolower($cfg->author)."/" : "");
        		$name .= strtolower($cfg->module_name);

        		// Append directory
        		$cfg->directory = $mod_dir;
        		$register[$name] = (array) $cfg;
        	} else {
        		// Get the name
        		$name = $mods[$i];

        		// Build a dynamic module config
        		$cfg = new stdClass();
        		$cfg->module_class = ucfirst($name);
        		$cfg->module_file = 'class.'.strtolower($name).".php";
        		$cfg->module_name = $name;
        		$cfg->dependencies = array();
        		$cfg->versions = array();
        		$cfg->directory = $mod_dir;
        		$register[$name] = (array)$cfg;
        	}
        }

        $this->mods->logger->stopLevel();
        $this->mods->config->set('modregister', 'register', $register);
        $this->mods->config->set('main', 'registers_last_update', date('U'));
	}
}


?>