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

/**
 * Logger Class
 *
 * The main tool to handle errors and exceptions. Provides some tools for debugging and tracking where errors take place
 * All fatal errors get catched by this class and get displayed if configured to do so.
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Logger {

	/**
	 * Log entries which display information entries
	 * @var array
	 */
 	public static $infoErrors = array();

 	/**
 	 * Log entries which display critical error entries
 	 * @var array
 	 */
 	public static $criticalErrors = array();

 	/**
 	 * Log entries which display warning entries
 	 * @var array
 	 */
 	public static $warningErrors = array();

 	/**
 	 * All log entries, unsorted
 	 * @var array
 	 */
 	public static $Logs = array();

 	/**
 	 * whether to output the log after FuzeWorks has run
 	 * @var boolean
 	 */
  	private static $print_to_screen = false;

  	/**
  	 * whether to output the log after FuzeWorks has run, regardless of conditions
  	 * @var boolean
  	 */
  	public static $debug = false;

  	/**
  	 * Initiates the Logger.
  	 *
  	 * Registers the error and exception handler, when required to do so by configuration
  	 */
	public static function init() {
 		// Register the error handler
 		if (Config::get('error')->error_reporting == true) {
 			set_error_handler(array('\FuzeWorks\Logger', "errorHandler"), E_ALL);
 			set_Exception_handler(array('\FuzeWorks\Logger', "exceptionHandler"));
 			error_reporting(false);
 		}
		self::$debug = Config::get('error')->debug;
 		self::newLevel("Logger Initiated");
	}

	/**
	 * Function to be run upon FuzeWorks shutdown.
	 *
	 * Logs a fatal error and outputs the log when configured or requested to do so
	 */
	public static function shutdown() {
		// Load last error if thrown
  		$errfile = "Unknown file";
 		$errstr = "shutdown";
 		$errno = E_CORE_ERROR;
 		$errline = 0;

 		$error = error_get_last();
 		if ($error !== NULL) {
 			$errno 		= $error['type'];
 			$errfile 	= $error['file'];
 			$errline 	= $error['line'];
 			$errstr 	= $error['message'];

			// Log it!
 			self::errorHandler($errno, $errstr, $errfile, $errline);
 			self::logInfo(self::backtrace());
 		}

		if (self::$debug == true || self::$print_to_screen) {
			self::log("Parsing debug log");
			echo self::logToScreen();
		}
	}

 	/**
 	 * System that redirects the errors to the appropriate logging method
 	 * @access public
 	 * @param int $type Error-type, Pre defined PHP Constant
 	 * @param string error. The error itself
 	 * @param string File. The absolute path of the file
 	 * @param int Line. The line on which the error occured.
 	 * @param array context. Some of the error's relevant variables
 	 * @return void
 	 */
	public static function errorHandler($type = E_USER_NOTICE, $error = "Undefined Error", $errFile = null, $errLine = null, $context = null) {
 		// Check type
 		$thisType = self::getType($type);
 		$LOG = array('type' => (!is_null($thisType) ? $thisType : "ERROR"),
 			'message' => (!is_null($error) ? $error : ""),
 			'logFile' => (!is_null($errFile) ? $errFile : ""),
 			'logLine' => (!is_null($errLine) ? $errLine : ""),
 			'context' => (!is_null($context) ? $context : ""),
 			'runtime' => round(self::getRelativeTime(), 4));
 		self::$Logs[] = $LOG;
 	}

	/**
	 * Exception handler
	 * Will be triggered when an uncaught exception occures. This function shows the error-message, and shuts down the script.
	 * Please note that most of the user-defined exceptions will be caught in the router, and handled with the error-controller.
	 *
	 * @access public
	 * @param Exception $exception The occured exception.
	 * @return void
	 */
	public static function exceptionHandler($exception)
	{
		$message = $exception->getMessage();
		$code = $exception->getCode();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$context = $exception->getTraceAsString();

		self::logError("Exception thrown: " . $message . " | " . $code, null, $file, $line);
	}

	/**
	 * Output the entire log to the screen. Used for debugging problems with your code.
	 * @return String   Output of the log
	 */
 	public static function logToScreen() {
 		// Send a screenLogEvent, allows for new screen log designs
 		$event = Events::fireEvent('screenLogEvent');
 		if ($event->isCancelled()) {
 			return false;
 		}

 		// Otherwise just load it
        $string = '<h3>FuzeWorks debug log</h3>';
        $layer = 0;
        for($i = 0; $i < count(self::$Logs); $i++){

            $log        = self::$Logs[$i];
            if($log['type'] == 'LEVEL_START'){
                $layer++;
                $color = 255-($layer*25);
                $string .= '<div style="background: rgb(188 , 232 ,'.$color.');border: 1px black solid;margin: 5px 0;padding: 5px 20px;">';
	            $string .= '<div style="font-weight: bold; font-size: 11pt;">'.$log['message'].'<span style="float: right">'.(!empty($log['runtime']) ? "(".round($log['runtime']*1000, 4).'ms)' : "").'</span></div>';
            } elseif ($log['type'] == "LEVEL_STOP") {
            	$layer--;
            	$string .= "</div>";
            } elseif ($log['type'] == "ERROR") {
	            $string .= '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt; background-color:#f56954;">['.$log['type'].']'.(!empty($log['context']) && is_string($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'
	            	<span style="float: right">'.(!empty($log['logFile']) ? $log['logFile'] : "")." : ".(!empty($log['logLine']) ? $log['logLine'] : "").'('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            } elseif ($log['type'] == "WARNING") {
	            $string .= '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt; background-color:#f39c12;">['.$log['type'].']'.(!empty($log['context']) && is_string($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'
	            	<span style="float: right">'.(!empty($log['logFile']) ? $log['logFile'] : "")." : ".(!empty($log['logLine']) ? $log['logLine'] : "").'('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            } elseif ($log['type'] == "INFO") {
	            $string .= '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt;">'.(!empty($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'<span style="float: right">('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            }
        }

        return $string;
 	}

 	/**
 	 * Backtrace a problem to the source using the trace of an Exception
 	 * @return string   HTML backtrace
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

	    for ($i = 0; $i < $length; $i++)
	    {
	        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
	    }

	    return "<b>BACKTRACE: <br/>\t" . implode("<br/>", $result)."</b>";
 	}

 	/* =========================================LOGGING METHODS==============================================================*/

 	/**
 	 * Create a information log entry
 	 * @param  String  $msg  The information to be logged
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
	public static function log($msg, $mod = null, $file = 0, $line = 0) {
		self::logInfo($msg, $mod, $file, $line);
	}

 	/**
 	 * Create a information log entry
 	 * @param  String  $msg  The information to be logged
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
 	public static function logInfo($msg, $mod = null, $file = 0, $line = 0) {
 		$LOG = array('type' => 'INFO',
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""),
 			'logLine' => (!is_null($line) ? $line : ""),
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round(self::getRelativeTime(), 4));

 		self::$infoErrors[] = $LOG;
 		self::$Logs[] = $LOG;
 	}

 	/**
 	 * Create a error log entry
 	 * @param  String  $msg  The information to be logged
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
 	public static function logError($msg, $mod = null, $file = 0, $line = 0)  {
 		$LOG = array('type' => 'ERROR',
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""),
 			'logLine' => (!is_null($line) ? $line : ""),
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round(self::getRelativeTime(), 4));

 		self::$criticalErrors[] = $LOG;
 		self::$Logs[] = $LOG;
 	}

 	/**
 	 * Create a warning log entry
 	 * @param  String  $msg  The information to be logged
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
 	public static function logWarning($msg, $mod = null, $file = 0, $line = 0)  {
 		$LOG = array('type' => 'WARNING',
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""),
 			'logLine' => (!is_null($line) ? $line : ""),
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round(self::getRelativeTime(), 4));

 		self::$warningErrors[] = $LOG;
 		self::$Logs[] = $LOG;
 	}

 	/**
 	 * Create a new Level log entry. Used to categorise logs
 	 * @param  String  $msg  The name of the new level
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
 	public static function newLevel($msg, $mod = null, $file = null, $line = null) {
 		$LOG = array('type' => 'LEVEL_START',
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""),
 			'logLine' => (!is_null($line) ? $line : ""),
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round(self::getRelativeTime(), 4));

 		self::$Logs[] = $LOG;
 	}

 	/**
 	 * Create a stop Level log entry. Used to close log categories
 	 * @param  String  $msg  The name of the new level
 	 * @param  String  $mod  The name of the module
 	 * @param  String  $file The file where the log occured
 	 * @param  integer $line The line where the log occured
 	 * @return void
 	 */
 	public static function stopLevel($msg = null, $mod = null, $file = null, $line = null) {
 		$LOG = array('type' => 'LEVEL_STOP',
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""),
 			'logLine' => (!is_null($line) ? $line : ""),
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round(self::getRelativeTime(), 4));

 		self::$Logs[] = $LOG;
 	}

 	/* =========================================OTHER METHODS==============================================================*/

	/**
	 * Returns a string representation of an error
	 * Turns a PHP error-constant (or integer) into a string representation.
	 *
	 * @access public
	 * @param int $type PHP-constant errortype (e.g. E_NOTICE).
	 * @return string String representation
	 */
	public static function getType($type) {

		switch ($type)
		{

			case E_ERROR:
				return "ERROR";
			case E_WARNING:
				return "WARNING";
			case E_PARSE:
				return "ERROR";
			case E_NOTICE:
				return "WARNING";
			case E_CORE_ERROR:
				return "ERROR";
			case E_CORE_WARNING:
				return "WARNING";
			case E_COMPILE_ERROR:
				return "ERROR";
			case E_COMPILE_WARNING:
				return "WARNING";
			case E_USER_ERROR:
				return "ERROR";
			case E_USER_WARNING:
				return "WARNING";
			case E_USER_NOTICE:
				return "WARNING";
			case E_USER_DEPRECATED:
				return "WARNING";
			case E_STRICT:
				return "ERROR";
			case E_RECOVERABLE_ERROR:
				return "ERROR";
			case E_DEPRECATED:
				return "WARNING";
		}

		return $type = 'Unknown error: '.$type;
	}

	/**
	 * Calls an HTTP error, sends it as a header, and loads a template if required to do so.
	 * @param  integer $errno HTTP error code
	 * @param  boolean $view  true to view error on website
	 */
    public static function http_error($errno = 500, $view = true){

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
            511 => 'Network Authentication Required'
        );

        self::logError('HTTP-error '.$errno.' called');
        self::log('Sending header HTTP/1.1 '.$errno.' '.$http_codes[$errno]);
        header('HTTP/1.1 '.$errno.' '.$http_codes[$errno]);

		// Do we want the error-view with it?
		if($view == false)
			return;

		// Load the view
        $view = 'errors/'.$errno;
        self::log('Loading view '.$view);

        // Try and load the view, if impossible, load HTTP code instead.
        try{
            Layout::view($view);
        } catch(LayoutException $exception){
            // No error page could be found, just echo the result
            echo "<h1>$errno</h1><h3>".$http_codes[$errno]."</h3>";
        }
    }

 	/**
 	 * Enable error to screen logging
 	 * @access public
 	 */
 	public static function enable() {
 		self::$print_to_screen = true;
 	}

 	/**
 	 * Disable error to screen logging
 	 * @access public
 	 */
 	public static function disable() {
 		self::$print_to_screen = false;
 	}

 	/**
 	 * Get the relative time since the framework started.
 	 *
 	 * Used for debugging timings in FuzeWorks
 	 * @return int Time passed since FuzeWorks init
 	 */
  	private static function getRelativeTime() {
 		$startTime = STARTTIME;
 		$time = microtime(true) - $startTime;
 		return $time;
 	}
}


?>