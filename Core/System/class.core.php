<?php

namespace FuzeWorks;
use \stdClass;
use \Exception;

/**
 * FuzeWorks Core
 * 
 * Holds all the modules and starts the framework. Allows for starting and managing modules
 */
class Core {

	public $mods;	
	public $register;
	
	/**
	 * An array which modules are loaded, and should not be loaded again
	 * @access private
	 * @var Array of module names
	 */
	private $loaded_modules = array();
	private $loaded = false;

	## START/STOP
	public function init() {
		if (!defined('STARTTIME')) {
			define('STARTTIME', microtime(true));
		}
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
		require_once("Core/System/class.abstract.bus.php");
		require_once("Core/System/class.abstract.event.php");
		require_once("Core/System/class.abstract.module.php");
		require_once("Core/System/class.abstract.model.php");
		require_once("Core/System/class.abstract.controller.php");
		require_once("Core/System/class.abstract.eventPriority.php");

		// Load the core classes
		require_once("Core/System/class.config.php");
		require_once("Core/System/class.logger.php");
		require_once("Core/System/class.models.php");
		require_once("Core/System/class.layout.php");
		require_once("Core/System/class.events.php");

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

	public function loadMod($name) {
		// Where the modules are
		$path = "Modules/";

		// Check if the requested module is registered
		if (isset($this->register[$name])) {
			if (!empty($this->register[$name])) {
				// Load the moduleInfo
				$cfg = (object) $this->register[$name];

				// Check if the module is already loaded. If so, only return a reference, if not, load the module
				if (in_array($name, $this->loaded_modules)) {
					// return the link
					$msg = "Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name)) . "' is already loaded";
					$this->mods->logger->log($msg);
					$c = &$this->mods->{strtolower($cfg->module_name)};
					return $c;
				} else {
					// Load the module
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
						$msg .= (isset($cfg->version) ? "; version: ".$cfg->version : "");
						$msg .= (isset($cfg->author) ? "; made by ".$cfg->author : "");
						$msg .= (isset($cfg->website) ? "; from ".$cfg->website: "");
						$this->mods->logger->log($msg);
					} else {
						// Throw Exception if the file does not exist
						throw new Exception("Requested mod '".$name."' could not be loaded. Class file not found", 1);
						return false;							
					}

					// If it is an abstract module, load an StdClass for the module address
					if (isset($cfg->abstract)) {
						if ($cfg->abstract) {
							$CLASS = new stdClass();
							return $this->mods->{strtolower($cfg->module_name)} = &$CLASS;
						}
					}

					// Load the module class
					$class_name = $cfg->module_class;
					$CLASS = new $class_name($this);

					// Apply default methods
					if (method_exists($CLASS, 'setModulePath')) {
						$CLASS->setModulePath($cfg->directory);
					}
					if (method_exists($CLASS, 'setModuleLinkName')) {
						$CLASS->setModuleLinkName(strtolower($cfg->module_name));
					}
					if (method_exists($CLASS, 'setModuleName')) {
						$CLASS->setModuleName($name);
					}

					if (!method_exists($CLASS, 'onLoad')) {
						throw new Exception("Module '".$name."' does not have an onLoad() method! Invalid module", 1);
					}
					$CLASS->onLoad();

					// Add to the loaded modules
					$this->loaded_modules[] = $name;

					// Return a reference
					return $this->mods->{strtolower($cfg->module_name)} = &$CLASS;
				}
			}
		}
	}

	public function buildRegister() {
		$this->mods->logger->newLevel("Loading Module Headers", 'Core');

		// Get all the module directories
		$dir = "Modules/";
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
        		if (isset($cfg->enabled)) {
        			if ($cfg->enabled) {
        				$register[$name] = (array) $cfg;
        				$this->mods->logger->log("[ON]  '".$name."'");
        			} else {
        				$this->mods->logger->log("[OFF] '".$name."'");
        			}
        		} else {
        			$register[$name] = (array) $cfg;
        			$this->mods->logger->log("[ON]  '".$name."'");
        		}
        		
        		
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
        		$this->mods->logger->log("[ON]  '".$name."'");
			}
		}

		$this->register = $register;
		$this->mods->logger->stopLevel();
		
	}
}


?>