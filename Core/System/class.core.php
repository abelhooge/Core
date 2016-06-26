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
        // Set the CWD for usage in the shutdown function+
        self::$cwd = getcwd();

        // If the environment is not yet defined, use production settings
        if (!defined('ENVIRONMENT'))
        {
            define('ENVIRONMENT', 'PRODUCTION');
        }
        
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

        // Disable events if requested to do so
        if (!$config->enable_events)
        {
            Events::disable();
        }

        // Build all the registers for correct operation, if modules are enabled
        if ($config->enable_modules)
        {
            Modules::buildRegister($config->registry_caching, 
                $config->registry_caching_method,
                $config->registry_caching_time
                );
        }

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
        include_once 'Core/System/class.factory.php';
        include_once 'Core/System/class.exceptions.php';
        include_once 'Core/System/class.abstract.event.php';

        // Load the core classes
        include_once 'Core/System/class.config.php';
        include_once 'Core/System/class.abstract.configOrmAbstract.php';
        include_once 'Core/System/class.configOrm.php';
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
        include_once 'Core/System/class.database.php';
        include_once 'Core/System/class.language.php';
        include_once 'Core/System/class.utf8.php';
        include_once 'Core/System/class.uri.php';
        include_once 'Core/System/class.security.php';
        include_once 'Core/System/class.input.php';
        include_once 'Core/System/class.output.php';

        // Load the core classes
        new Factory();

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
        $event = Events::fireEvent('coreShutdownEvent');

        if ($event->isCancelled() === false)
        {
            // If the output should be displayed, send the final render and parse the logger
            Logger::shutdownError();
            Factory::getInstance()->output->_display();
            Logger::shutdown();
        }
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
            Logger::loadComposer();

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

    public static function isCli()
    {
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }

    /**
     * Is HTTPS?
     *
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return  bool
     */
    public static function isHttps()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link    https://bugs.php.net/bug.php?id=54709
     * @param   string
     * @return  bool
     */
    public static function isReallyWritable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && (self::isPHP('5.4') OR ! ini_get('safe_mode')))
        {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file))
        {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE)
            {
                return FALSE;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
        {
            return FALSE;
        }

        fclose($fp);
        return TRUE;
    }

    /**
     * Set HTTP Status Header
     *
     * @param   int the status code
     * @param   string
     * @return  void
     */
    public static function setStatusHeader($code = 200, $text = '')
    {
        if (self::isCli())
        {
            return;
        }

        if (empty($code) OR ! is_numeric($code))
        {
            throw new Exception('Status codes must be numeric', 1);
        }

        if (empty($text))
        {
            is_int($code) OR $code = (int) $code;
            $stati = array(
                100 => 'Continue',
                101 => 'Switching Protocols',

                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',

                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',

                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                422 => 'Unprocessable Entity',

                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
            );

            if (isset($stati[$code]))
            {
                $text = $stati[$code];
            }
            else
            {
                throw new CoreException('No status text available. Please check your status code number or supply your own message text.', 1);
            }
        }

        if (strpos(PHP_SAPI, 'cgi') === 0)
        {
            header('Status: '.$code.' '.$text, TRUE);
        }
        else
        {
            $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($server_protocol.' '.$code.' '.$text, TRUE, $code);
        }
    }
}
