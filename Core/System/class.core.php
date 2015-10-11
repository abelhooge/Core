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
	public static $version = "0.0.1";

    /**
     * @var bool Whether the files has been loaded
     */
    private static $loaded = false;

	/**
	 * Initializes the core
	 *
	 * @throws \Exception
	 */
	public static function init() {
		// Defines the time the framework starts. Used for timing functions in the framework
		if (!defined('STARTTIME')) {
			define('STARTTIME', microtime(true));
		}

		// Load basics
		ignore_user_abort(true);
		register_shutdown_function(array('\FuzeWorks\Core', "shutdown"));

		// Load core functionality
		self::loadStartupFiles();

		// Load the config file of the FuzeWorks core
		$config = Config::get('core');

		// Load the logger
		Logger::init();

		// And initialize the router paths
		Router::init();

		// Build all the registers for correct operation
		Modules::buildRegister();
		Events::buildEventRegister();

		// Load Composer
		if ($config->enable_composer) {
			$file = ($config->composer_autoloader != '' ? $config->composer_autoloader : 'vendor/autoload.php');
			self::loadComposer($file);
		}

		$event = Events::fireEvent('coreStartEvent');
		if ($event->isCancelled()) {
			return true;
		}
	}

	/**
	 * Load all the files of the FuzeWorks Framework.
	 */
	private static function loadStartupFiles() {
		if (self::$loaded)
			return;

		// Load core abstracts
		require_once("Core/System/class.exceptions.php");
		require_once("Core/System/class.abstract.event.php");

		// Load the core classes
		require_once("Core/System/class.config.php");
		require_once("Core/System/class.abstract.eventPriority.php");
		require_once("Core/System/class.events.php");
		require_once("Core/System/class.logger.php");
		require_once("Core/System/class.abstract.model.php");
		require_once("Core/System/class.models.php");
		require_once("Core/System/class.layout.php");
		require_once("Core/System/class.abstract.controllerabstract.php");
		require_once("Core/System/class.router.php");
		require_once("Core/System/class.abstract.module.php");
		require_once("Core/System/class.modules.php");

		// Create the module holder
		new Config();
		new Events();
		new Logger();
		new Models();
		new Layout();
		new Router();
		new Modules();

        self::$loaded = true;
	}

	/**
	 * Stop FuzeWorks and run all shutdown functions.
	 *
	 * Afterwards run the Logger shutdown function in order to possibly display the log
	 */
	public static function shutdown() {
		Events::fireEvent('coreShutdownEvent');
		Logger::shutdown();
	}

	/**
	 * Load composer if it is present
	 * @access private
	 * @param String directory of composer autoload file (optional)
	 * @return boolean true on success, false on failure
	 */
	private static function loadComposer($file = "vendor/autoload.php") {
		if (file_exists($file)) {
			require($file);
			Logger::log('Loaded Composer');
			return true;
		}
		Logger::log('Failed to load Composer. File \''.$file.'\' not found');
		return false;
	}


}


?>