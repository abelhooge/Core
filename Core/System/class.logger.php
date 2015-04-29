<?php

namespace FuzeWorks;
use \Exception;

class Logger extends Bus{

 	public $infoErrors = array();
 	public $criticalErrors = array();
 	public $warningErrors = array();
 	public $Logs = array();
  	private $print_to_screen = false;

	public function __construct(&$core) {
		parent::__construct($core);

 		// Register the error handler
 		if ($this->config->error->error_reporting == true) {
 			set_error_handler(array($this, "errorHandler"), E_ALL);
 			set_Exception_handler(array($this, "exceptionHandler"));
 			error_reporting(false);
 		}
		$this->events->addListener(array($this, 'shutdown'), 'coreShutdownEvent', EventPriority::LOWEST);
 		$this->newLevel("Logger Initiated");
	}

	public function shutdown() {
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
 			$this->errorHandler($errno, $errstr, $errfile, $errline);
 		}

		if ($this->mods->config->error->debug == true || $this->print_to_screen) {
			$this->log("Parsing debug log", "Logger");
			$this->logToScreen();
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
	public function errorHandler($type = E_USER_NOTICE, $error = "Undefined Error", $errFile = null, $errLine = null, $context = null) {
 		// Check type
 		$thisType = self::getType($type);
 		$LOG = array('type' => (!is_null($thisType) ? $thisType : "ERROR"), 
 			'message' => (!is_null($error) ? $error : ""), 
 			'logFile' => (!is_null($errFile) ? $errFile : ""), 
 			'logLine' => (!is_null($errLine) ? $errLine : ""), 
 			'context' => (!is_null($context) ? $context : ""),
 			'runtime' => round($this->getRelativeTime(), 4));
 		$this->Logs[] = $LOG;
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
	public function exceptionHandler($exception)
	{
		$message = $exception->getMessage();
		$code = $exception->getCode();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$context = $exception->getTraceAsString();

		$this->logError("Exception thrown: " . $message . " | " . $code, null, $file, $line);
	}

 	public function logToScreen() {
        echo '<h3>FuzeWorks debug log</h3>';
        $layer = 0;
        for($i = 0; $i < count($this->Logs); $i++){

            $log        = $this->Logs[$i];
            if($log['type'] == 'LEVEL_START'){
                $layer++;
                $color = 255-($layer*25);
                echo '<div style="background: rgb(188 , 232 ,'.$color.');border: 1px black solid;margin: 5px 0;padding: 5px 20px;">';
	            echo '<div style="font-weight: bold; font-size: 11pt;">'.$log['message'].'<span style="float: right">'.(!empty($log['runtime']) ? "(".round($log['runtime']*1000, 4).'ms)' : "").'</span></div>';
            } elseif ($log['type'] == "LEVEL_STOP") {
            	$layer--;
            	echo "</div>";
            } elseif ($log['type'] == "ERROR") {
	            echo '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt; background-color:#f56954;">['.$log['type'].']'.(!empty($log['context']) && is_string($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'
	            	<span style="float: right">'.(!empty($log['logFile']) ? $log['logFile'] : "")." : ".(!empty($log['logLine']) ? $log['logLine'] : "").'('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            } elseif ($log['type'] == "WARNING") {
	            echo '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt; background-color:#f39c12;">['.$log['type'].']'.(!empty($log['context']) && is_string($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'
	            	<span style="float: right">'.(!empty($log['logFile']) ? $log['logFile'] : "")." : ".(!empty($log['logLine']) ? $log['logLine'] : "").'('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            } elseif ($log['type'] == "INFO") {
	            echo '<div style="'.($layer == 0 ? 'padding-left: 21px;' : "").'font-size: 11pt;">'.(!empty($log['context']) ? '<u>['.$log['context'].']</u>' : "").' '.$log["message"].'<span style="float: right">('.round($log['runtime']*1000, 4).' ms)</span></div>';   	
            }
        }
 	}

 	public function backtrace() {
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
	    
	    return "\t" . implode("<br/>", $result);
 	}

 	/* =========================================LOGGING METHODS==============================================================*/

 	
	public function log($msg, $mod = null, $file = 0, $line = 0) {
		$this->logInfo($msg, $mod, $file, $line);
	}

 	public function logInfo($msg, $mod = null, $file = 0, $line = 0) {
 		$LOG = array('type' => 'INFO', 
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""), 
 			'logLine' => (!is_null($line) ? $line : ""), 
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round($this->getRelativeTime(), 4)); 

 		$this->infoErrors[] = $LOG;
 		$this->Logs[] = $LOG;
 	}

 	public function logError($msg, $mod = null, $file = 0, $line = 0)  {
 		$LOG = array('type' => 'ERROR', 
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""), 
 			'logLine' => (!is_null($line) ? $line : ""), 
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round($this->getRelativeTime(), 4)); 
 		
 		$this->criticalErrors[] = $LOG;
 		$this->Logs[] = $LOG;
 	}

 	public function logWarning($msg, $mod = null, $file = 0, $line = 0)  {
 		$LOG = array('type' => 'WARNING', 
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""), 
 			'logLine' => (!is_null($line) ? $line : ""), 
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round($this->getRelativeTime(), 4)); 
 		
 		$this->warningErrors[] = $LOG;
 		$this->Logs[] = $LOG;
 	}

 	public function newLevel($msg, $mod = null, $file = null, $line = null) {
 		$LOG = array('type' => 'LEVEL_START', 
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""), 
 			'logLine' => (!is_null($line) ? $line : ""), 
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round($this->getRelativeTime(), 4)); 

 		$this->Logs[] = $LOG;
 	}

 	public function stopLevel($msg = null, $mod = null, $file = null, $line = null) {
 		$LOG = array('type' => 'LEVEL_STOP', 
 			'message' => (!is_null($msg) ? $msg : ""),
 			'logFile' => (!is_null($file) ? $file : ""), 
 			'logLine' => (!is_null($line) ? $line : ""), 
 			'context' => (!is_null($mod) ? $mod : ""),
 			'runtime' => round($this->getRelativeTime(), 4)); 

 		$this->Logs[] = $LOG;
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
	public function getType($type) {
		
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

    public function http_error($errno){
        
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

        $this->logError('HTTP-error '.$errno.' called', 'FuzeWorks->Logger');
        $this->logInfo('Sending header HTTP/1.1 '.$errno.' '.$http_codes[$errno], 'FuzeWorks->Logger', __FILE__, __LINE__);
        header('HTTP/1.1 '.$errno.' '.$http_codes[$errno]);
    }

 	/** 
 	 * Enable error to screen logging
 	 * @access public
 	 */
 	public function enable() {
 		$this->print_to_screen = true;
 	}

 	/** 
 	 * Disable error to screen logging
 	 * @access public
 	 */
 	public function disable() {
 		$this->print_to_screen = false;
 	}

  	private function getRelativeTime() {
 		$startTime = STARTTIME;
 		$time = microtime(true) - $startTime;
 		return $time;
 	}
}


?>