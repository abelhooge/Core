<?php

namespace FuzeWorks;
use \stdClass;

/**
 * FuzeWorks Core
 * 
 * Holds all the modules and starts the framework. Allows for starting and managing modules
 */
class Core {

	/**
	 * The current version of the framework
	 * @access public
	 * @var String Framework version
	 */
	public $version = "0.0.1";
    private $loaded = false;
   	public $mods;

	## START/STOP
	public function init() {
		if (!defined('STARTTIME')) {
			define('STARTTIME', microtime(true));
		}
		// Load basics
		ignore_user_abort(true);
		register_shutdown_function(array($this, "shutdown"));

		// Load core functionality
		$this->loadStartupFiles();

		$this->mods->modules->buildRegister();
		$this->mods->events->buildEventRegister();

		// And initialize the router paths
		$this->mods->router->init();

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
		require_once("Core/System/class.exceptions.php");

		// Load the core classes
		require_once("Core/System/class.config.php");
		require_once("Core/System/class.logger.php");
		require_once("Core/System/class.models.php");
		require_once("Core/System/class.layout.php");
		require_once("Core/System/class.events.php");
		require_once("Core/System/class.router.php");
		require_once("Core/System/class.modules.php");

		// Create the module holder
		

		// Load the modules
        $this->mods->events 		= new Events 		($this);
		$this->mods->config	   		= new Config 		($this);
        $this->mods->logger      	= new Logger 		($this);
        $this->mods->models 		= new Models 		($this);
        $this->mods->layout 		= new Layout 		($this);
        $this->mods->router 		= new Router 		($this);
        $this->mods->modules 		= new Modules 		($this);

        $this->loaded = true;
	}

	public function shutdown() {
		$this->mods->events->fireEvent('coreShutdownEvent');
		$this->mods->logger->shutdown();
	}

	public function loadMod($name) {
		return $this->mods->modules->loadMod($name);
	}

	public function addMod($moduleInfo_file) {
		return $this->mods->modules->addModule($moduleInfo_file);
	}


}


?>