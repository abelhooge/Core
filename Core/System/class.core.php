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

	## START/STOP
	public function init() {
		// Load basics
		ignore_user_abort(true);
		register_shutdown_function(array($this, "shutdown"));

		// Load core functionality
		$this->mods = new stdClass();
		$this->loadStartupFiles();
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
	public function loadMod($name) {
		// Class name
		$class_name = ucfirst($name);
		// Check if mod is already loaded
		if (!isset($this->mods->$name)) {
			// Check if class is already included

			// If the class is not loaded, load it
			if (!class_exists($class_name)) {

				// If the mod is in the top mod directory, load it directly
				$file = FUZEPATH . "/Core/Mods/class.".$class_name.".php";
				if (file_exists($file)) {
					$this->mods->logger->log("Loading module '".$class_name."'");
					$path = FUZEPATH . "/Core/Mods/class.".$class_name.".php";
					require_once($file);

				// If not, and a mod config file is found, follow that
				} elseif ( file_exists(FUZEPATH . "/Core/Mods/".strtolower($name)."/moduleInfo.php" )) {
					// Load the config file
					$cfg = (object) require(FUZEPATH . "/Core/Mods/".strtolower($name)."/moduleInfo.php");

					// Load the class name and file
					$class_file = FUZEPATH . "/Core/Mods/".strtolower($name)."/" . $cfg->module_file;
					$class_name = $cfg->module_class;

					// Load the dependencies first
					$deps = (isset($cfg->dependencies) ? $cfg->dependencies : array());
					for ($i=0; $i < count($deps); $i++) { 
						$this->loadMod($deps[$i]);
					}

					$path = FUZEPATH . "/Core/Mods/".strtolower($name)."/";
					$this->mods->logger->log("Loading Module '".$cfg->name."' v".$cfg->version." made by '".$cfg->author."' : '".$cfg->website."'");

					require_once($class_file);

				// If no config file found, but a main class is, load that
				} elseif ( file_exists(FUZEPATH . "/Core/Mods/".strtolower($name)."/class.".$class_name.".php") ){
					$this->mods->logger->log("Loading module '".$class_name."'");
					$path = FUZEPATH . "/Core/Mods/".strtolower($name)."/";
					require_once(FUZEPATH . "/Core/Mods/".strtolower($name)."/class.".$class_name.".php");

				// Otherwise Abort
				} else {
					// MOD NOT FOUND
					throw new Exception("Requested mod '".$name."' was not found", 1);
					return false;
				}
			}

			// Create class object
			$CLASS = new $class_name($this);
			if (method_exists($CLASS, 'setModulePath')) {
				$CLASS->setModulePath($path);
			}
			$this->mods->{strtolower($name)}  = &$CLASS;
			$CLASS->onLoad();
		}
	}
}


?>