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
use \stdClass;

/**
 * FuzeWorks Core
 *
 * Holds all the modules and starts the framework. Allows for starting and managing modules
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
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

		// Load Composer
		if ($this->mods->config->core->enable_composer) {
			$this->loadComposer();
		}

		$this->mods->modules->buildRegister();
		$this->mods->events->buildEventRegister();

		// And initialize the router paths
		$this->mods->router->init();

		$event = $this->mods->events->fireEvent('coreStartEvent');
		if ($event->isCancelled()) {
			return true;
		}
	}

	public function loadStartupFiles($config = array()) {
		if ($this->loaded)
			return;

		// Load core abstracts
		require_once("Core/System/class.abstract.bus.php");
		require_once("Core/System/class.catcher.php");
		require_once("Core/System/class.exceptions.php");
		require_once("Core/System/class.abstract.event.php");

		// Load the core classes
		require_once("Core/System/class.config.php");

		// Create the module holder
		$this->mods->config	   		= new Config 		($this);
		$core_mods = $this->mods->config->core;

		// Events
		if ($core_mods->enable_events) {
			require_once("Core/System/class.abstract.eventPriority.php");
			require_once("Core/System/class.events.php");
			$this->mods->events 	= new Events 		($this);
		} else {
			$this->mods->events 	= new EventsCatcher ($this);
		}

		// Logger
		if ($core_mods->enable_logger) {
			require_once("Core/System/class.logger.php");
			$this->mods->logger 	= new Logger 		($this);
		} else {
			$this->mods->logger 	= new Catcher 		($this);
		}

		// Models
		if ($core_mods->enable_models) {
			require_once("Core/System/class.abstract.model.php");
			require_once("Core/System/class.models.php");
			$this->mods->models 	= new Models 		($this);
		} else {
			$this->mods->models 	= new Catcher 		($this);
		}

		// Layout
		if ($core_mods->enable_layout) {
			require_once("Core/System/class.layout.php");
			$this->mods->layout 	= new Layout 		($this);
		} else {
			$this->mods->layout 	= new Catcher 		($this);
		}

		// Router
		if ($core_mods->enable_router) {
			require_once("Core/System/class.abstract.controller.php");
			require_once("Core/System/class.router.php");
			$this->mods->router 	= new Router 		($this);
		} else {
			$this->mods->router 	= new Catcher 		($this);
		}

		// Modules
		if ($core_mods->enable_modules) {
			require_once("Core/System/class.abstract.module.php");
			require_once("Core/System/class.modules.php");
			$this->mods->modules 	= new Modules 		($this);
		} else {
			$this->mods->modules 	= new Catcher 		($this);
		}

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

	/**
	 * Load composer if it is present
	 * @access private
	 * @param String directory of composer autoload file (optional)
	 */
	private function loadComposer($file = "vendor/autoload.php") {
		if (file_exists($file)) {
			require($file);
		}
	}


}


?>