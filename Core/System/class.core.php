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
 * FuzeWorks Core.
 *
 * Holds all the modules and starts the framework. Allows for starting and managing modules
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Core
{
    /**
     * The current version of the framework.
     *
     * @var string Framework version
     */
    public static $version = '0.0.1';

    /**
     * @var bool Whether the files has been loaded
     */
    private static $loaded = false;

    /**
     * Working directory of the Framework.
     *
     * This is required to make the shutdown function working under Apache webservers
     *
     * @var string
     */
    public static $cwd;

    /**
     * Initializes the core.
     *
     * @throws \Exception
     */
    public static function init()
    {
        // Defines the time the framework starts. Used for timing functions in the framework
        if (!defined('STARTTIME')) {
            define('STARTTIME', microtime(true));
            define('DS', DIRECTORY_SEPARATOR);
        }

        // Load basics
        ignore_user_abort(true);
        register_shutdown_function(array('\FuzeWorks\Core', 'shutdown'));

        // Load core functionality
        self::loadStartupFiles();

        // Load the config file of the FuzeWorks core
        $config = Config::get('core');

        // Load the logger
        Logger::init();

        // And initialize the router paths
        Router::init();

        // Build all the registers for correct operation
        Modules::buildRegister($config->registry_caching, 
            $config->registry_caching_method,
            $config->registry_caching_time
            );

        // Load Composer
        if ($config->enable_composer) {
            $file = ($config->composer_autoloader != '' ? $config->composer_autoloader : 'vendor/autoload.php');
            self::loadComposer($file);
        }

        // And fire the coreStartEvent
        $event = Events::fireEvent('coreStartEvent');
        if ($event->isCancelled()) {
            return true;
        }

        // Set the CWD for usage in the shutdown function+
        self::$cwd = getcwd();
    }

    /**
     * Load all the files of the FuzeWorks Framework.
     */
    private static function loadStartupFiles()
    {
        if (self::$loaded) {
            return;
        }

        // Load core abstracts
        include_once 'Core/System/class.exceptions.php';
        include_once 'Core/System/class.abstract.event.php';

        // Load the core classes
        include_once 'Core/System/class.config.php';
        include_once 'Core/System/class.abstract.eventPriority.php';
        include_once 'Core/System/class.events.php';
        include_once 'Core/System/class.logger.php';
        include_once 'Core/System/class.abstract.model.php';
        include_once 'Core/System/class.models.php';
        include_once 'Core/System/class.layout.php';
        include_once 'Core/System/class.abstract.controllerabstract.php';
        include_once 'Core/System/class.router.php';
        include_once 'Core/System/class.abstract.module.php';
        include_once 'Core/System/class.modules.php';
        include_once 'Core/System/class.libraries.php';
        include_once 'Core/System/class.helpers.php';

        // Load the core classes
        new Config();
        new Logger();
        new Events();
        new Models();
        new Layout();
        new Router();
        new Modules();
        new Libraries();
        new Helpers();

        self::$loaded = true;
    }

    /**
     * Stop FuzeWorks and run all shutdown functions.
     *
     * Afterwards run the Logger shutdown function in order to possibly display the log
     */
    public static function shutdown()
    {
        // Fix Apache bug where CWD is changed upon shutdown
        chdir(self::$cwd);

        // Fire the Shutdown event
        Events::fireEvent('coreShutdownEvent');

        // And end the logger
        Logger::shutdown();
    }

    /**
     * Load composer if it is present.
     *
     * @param string directory of composer autoload file (optional)
     *
     * @return bool true on success, false on failure
     */
    private static function loadComposer($file = 'vendor/autoload.php')
    {
        if (file_exists($file)) {
            include $file;
            Logger::log('Loaded Composer');

            return true;
        }
        Logger::log('Failed to load Composer. File \''.$file.'\' not found');

        return false;
    }

    /**
     * Checks whether the current running version of PHP is equal to the input string.
     *
     * @param   string
     * @return  bool    true if running higher than input string
     *
     */
    public static function isPHP($version)
    {
        static $_is_php;
        $version = (string) $version;

        if ( ! isset($_is_php[$version]))
        {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $_is_php[$version];
    }
}
