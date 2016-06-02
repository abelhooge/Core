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
 * Logger Class.
 *
 * The main tool to handle errors and exceptions. Provides some tools for debugging and tracking where errors take place
 * All fatal errors get catched by this class and get displayed if configured to do so.
 * Also provides utilities to benchmark the application.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Logger {

    /**
     * Log entries which display information entries.
     *
     * @var array
     */
    public static $infoErrors = array();

    /**
     * Log entries which display debugging entries.
     *
     * @var array
     */
    public static $debugErrors = array();

    /**
     * Log entries which display critical error entries.
     *
     * @var array
     */
    public static $criticalErrors = array();

    /**
     * Log entries which display warning entries.
     *
     * @var array
     */
    public static $warningErrors = array();

    /**
     * All log entries, unsorted.
     *
     * @var array
     */
    public static $Logs = array();

    /**
     * whether to output the log after FuzeWorks has run.
     *
     * @var bool
     */
    private static $print_to_screen = false;

    /**
     * whether to output the log to a file after FuzeWorks has run.
     *
     * @var bool
     */
    private static $log_to_file = false;

    /**
     * The template to use when parsing the debug log
     * 
     * @var string Template name
     */
    private static $logger_template = 'logger_default';

    /**
     * whether to output the log after FuzeWorks has run, regardless of conditions.
     *
     * @var bool
     */
    public static $debug = false;

    /**
     * List of all benchmark markpoints.
     * 
     * @var array
     */
    public static $markPoints = array();

    /**
     * Initiates the Logger.
     *
     * Registers the error and exception handler, when required to do so by configuration
     */
    public function __construct() {
        // Register the error handler
        if (Config::get('error')->error_reporting == true) {
            set_error_handler(array('\FuzeWorks\Logger', 'errorHandler'), E_ALL);
            set_Exception_handler(array('\FuzeWorks\Logger', 'exceptionHandler'));
            error_reporting(false);
        }
        self::$debug = Config::get('error')->debug;
        self::$log_to_file = Config::get('error')->log_to_file;
        self::$logger_template = Config::get('error')->logger_template;
        self::newLevel('Logger Initiated');
    }

    /**
     * Function to be run upon FuzeWorks shutdown.
     *
     * Logs a fatal error and outputs the log when configured or requested to do so
     */
    public static function shutdown() {
        // Load last error if thrown
        $errfile = 'Unknown file';
        $errstr = 'shutdown';
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();
        if ($error !== null) {
            $errno = $error['type'];
            $errfile = $error['file'];
            $errline = $error['line'];
            $errstr = $error['message'];

            // Log it!
            self::errorHandler($errno, $errstr, $errfile, $errline);
        }

        // And finally stop the Logging
        self::stopLevel();

        if (self::$debug == true || self::$print_to_screen) {
            self::log('Parsing debug log');
            self::logToScreen();
        }

        if (self::$log_to_file == true)
        {
            self::logToFile();
        }
    }

    /**
     * System that redirects the errors to the appropriate logging method.
     *
     * @param int $type Error-type, Pre defined PHP Constant
     * @param string error. The error itself
     * @param string File. The absolute path of the file
     * @param int Line. The line on which the error occured.
     * @param array context. Some of the error's relevant variables
     */
    public static function errorHandler($type = E_USER_NOTICE, $error = 'Undefined Error', $errFile = null, $errLine = null, $context = null) {
        // Check type
        $thisType = self::getType($type);
        $LOG = array('type' => (!is_null($thisType) ? $thisType : 'ERROR'),
            'message' => (!is_null($error) ? $error : ''),
            'logFile' => (!is_null($errFile) ? $errFile : ''),
            'logLine' => (!is_null($errLine) ? $errLine : ''),
            'context' => (!is_null($context) ? $context : ''),
            'runtime' => round(self::getRelativeTime(), 4),);
        self::$Logs[] = $LOG;
    }

    /**
     * Exception handler
     * Will be triggered when an uncaught exception occures. This function shows the error-message, and shuts down the script.
     * Please note that most of the user-defined exceptions will be caught in the router, and handled with the error-controller.
     *
     * @param Exception $exception The occured exception.
     */
    public static function exceptionHandler($exception) {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $context = $exception->getTraceAsString();

        self::logError('Exception thrown: ' . $message . ' | ' . $code, null, $file, $line);
    }

    /**
     * Set the template that FuzeWorks should use to parse debug logs
     * 
     * @var string Name of the template file
     */
    public static function setLoggerTemplate($templateName)
    {
        self::$logger_template = $templateName;
    }

    /**
     * Output the entire log to the screen. Used for debugging problems with your code.
     *
     * @return string Output of the log
     */
    public static function logToScreen() {
        // Send a screenLogEvent, allows for new screen log designs
        $event = Events::fireEvent('screenLogEvent');
        if ($event->isCancelled()) {
            return false;
        }

        Layout::reset();
        Layout::assign('Logs', self::$Logs);
        Layout::view(self::$logger_template, 'Core'.DS.'Views');
    }

    public static function logToFile()
    {
        Layout::reset();
        Layout::assign('Logs', self::$Logs);
        $contents = Layout::get('logger_cli', 'Core'.DS.'Views');
        $file = 'Application'.DS.'Logs'.DS.'log_latest.php';
        if (is_writable($file))
        {
            file_put_contents($file, '<?php ' . $contents);
        }
    }

    /**
     * Backtrace a problem to the source using the trace of an Exception.
     *
     * @return string HTML backtrace
     */
    public static function backtrace() {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; ++$i) {
            $result[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return "<b>BACKTRACE: <br/>\t" . implode('<br/>', $result) . '</b>';
    }

    /* =========================================LOGGING METHODS============================================================== */

    /**
     * Set a benchmark markpoint.
     * 
     * Multiple calls to this function can be made so that several
     * execution points can be timed.
     * 
     * @param   string    $name   Marker name
     * @return  void
     */
    public static function mark($name) {
        self::$markPoints[$name] = microtime(TRUE);
    }

    /**
     * Create a information log entry.
     *
     * @param string $msg  The information to be logged
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function log($msg, $mod = null, $file = 0, $line = 0) {
        self::logInfo($msg, $mod, $file, $line);
    }

    /**
     * Create a information log entry.
     *
     * @param string $msg  The information to be logged
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function logInfo($msg, $mod = null, $file = 0, $line = 0) {
        $LOG = array('type' => 'INFO',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$infoErrors[] = $LOG;
        self::$Logs[] = $LOG;
    }

    /**
     * Create a information log entry.
     *
     * @param string $msg  The information to be logged
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function logDebug($msg, $mod = null, $file = 0, $line = 0) {
        $LOG = array('type' => 'DEBUG',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$debugErrors[] = $LOG;
        self::$Logs[] = $LOG;
    }

    /**
     * Create a error log entry.
     *
     * @param string $msg  The information to be logged
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function logError($msg, $mod = null, $file = 0, $line = 0) {
        $LOG = array('type' => 'ERROR',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$criticalErrors[] = $LOG;
        self::$Logs[] = $LOG;
    }

    /**
     * Create a warning log entry.
     *
     * @param string $msg  The information to be logged
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function logWarning($msg, $mod = null, $file = 0, $line = 0) {
        $LOG = array('type' => 'WARNING',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$warningErrors[] = $LOG;
        self::$Logs[] = $LOG;
    }

    /**
     * Create a new Level log entry. Used to categorise logs.
     *
     * @param string $msg  The name of the new level
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function newLevel($msg, $mod = null, $file = null, $line = null) {
        $LOG = array('type' => 'LEVEL_START',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$Logs[] = $LOG;
    }

    /**
     * Create a stop Level log entry. Used to close log categories.
     *
     * @param string $msg  The name of the new level
     * @param string $mod  The name of the module
     * @param string $file The file where the log occured
     * @param int    $line The line where the log occured
     */
    public static function stopLevel($msg = null, $mod = null, $file = null, $line = null) {
        $LOG = array('type' => 'LEVEL_STOP',
            'message' => (!is_null($msg) ? $msg : ''),
            'logFile' => (!is_null($file) ? $file : ''),
            'logLine' => (!is_null($line) ? $line : ''),
            'context' => (!is_null($mod) ? $mod : ''),
            'runtime' => round(self::getRelativeTime(), 4),);

        self::$Logs[] = $LOG;
    }

    /* =========================================OTHER METHODS============================================================== */

    /**
     * Returns a string representation of an error
     * Turns a PHP error-constant (or integer) into a string representation.
     *
     * @param int $type PHP-constant errortype (e.g. E_NOTICE).
     *
     * @return string String representation
     */
    public static function getType($type) {
        switch ($type) {
            case E_ERROR:
                return 'ERROR';
            case E_WARNING:
                return 'WARNING';
            case E_PARSE:
                return 'ERROR';
            case E_NOTICE:
                return 'WARNING';
            case E_CORE_ERROR:
                return 'ERROR';
            case E_CORE_WARNING:
                return 'WARNING';
            case E_COMPILE_ERROR:
                return 'ERROR';
            case E_COMPILE_WARNING:
                return 'WARNING';
            case E_USER_ERROR:
                return 'ERROR';
            case E_USER_WARNING:
                return 'WARNING';
            case E_USER_NOTICE:
                return 'WARNING';
            case E_USER_DEPRECATED:
                return 'WARNING';
            case E_STRICT:
                return 'ERROR';
            case E_RECOVERABLE_ERROR:
                return 'ERROR';
            case E_DEPRECATED:
                return 'WARNING';
        }

        return $type = 'Unknown error: ' . $type;
    }

    /**
     * Calls an HTTP error, sends it as a header, and loads a template if required to do so.
     *
     * @param int  $errno HTTP error code
     * @param bool $view  true to view error on website
     */
    public static function http_error($errno = 500, $view = true) {
        $http_codes = array(
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
            418 => 'I\'m a teapot',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        );

        self::logError('HTTP-error ' . $errno . ' called');
        self::log('Sending header HTTP/1.1 ' . $errno . ' ' . $http_codes[$errno]);
        header('HTTP/1.1 ' . $errno . ' ' . $http_codes[$errno]);

        // Do we want the error-view with it?
        if ($view == false) {
            return;
        }

        // Load the view
        $view = 'errors/' . $errno;
        self::log('Loading view ' . $view);

        // Try and load the view, if impossible, load HTTP code instead.
        try {
            Layout::view($view);
        } catch (LayoutException $exception) {
            // No error page could be found, just echo the result
            echo "<h1>$errno</h1><h3>" . $http_codes[$errno] . '</h3>';
        }
    }

    /**
     * Enable error to screen logging.
     */
    public static function enable() {
        self::$print_to_screen = true;
    }

    /**
     * Disable error to screen logging.
     */
    public static function disable() {
        self::$print_to_screen = false;
    }

    /**
     * Get the relative time since the framework started.
     *
     * Used for debugging timings in FuzeWorks
     *
     * @return int Time passed since FuzeWorks init
     */
    private static function getRelativeTime() {
        $startTime = STARTTIME;
        $time = microtime(true) - $startTime;

        return $time;
    }

    /**
     * Elapsed time
     *
     * Calculates the time difference between two marked points.
     *
     * If the first parameter is empty this function instead returns the
     * {elapsed_time} pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     *
     * @param	string	$point1		A particular marked point
     * @param	string	$point2		A particular marked point
     * @param	int	$decimals	Number of decimal places
     *
     * @return	string	Calculated elapsed time on success,
     * 			an '{elapsed_string}' if $point1 is empty
     * 			or an empty string if $point1 is not found.
     */
    public static function elapsedTime($point1 = '', $point2 = '', $decimals = 4) {
        if ($point1 === '') {
            return '{elapsed_time}';
        }

        if (!isset(self::$markPoints[$point1])) {
            return '';
        }

        if (!isset(self::$markPoints[$point2])) {
            self::$markPoints[$point2] = microtime(TRUE);
        }

        return number_format(self::$markPoints[$point2] - self::$markPoints[$point1], $decimals);
    }

}
