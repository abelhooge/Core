<?php

namespace FuzeWorks;

/**
 * Catcher Class
 * 
 * This class catches requests and returns nothing. Handy for a temporary replacement object
 */
class Catcher extends Bus{

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function __get($name) {
		return new Catcher($this->core);
	}

	public function __set($name, $value) {}

	public function __unset($name) {}

	public function __isset($name) {}

	public function __call($name, $params) {
		return new Catcher($this->core);
	}

	public static function __callStatic($name, $params) {}

	public function __sleep() {}

	public function __wakeup() {}

	public function __toString() {}

}

class EventsCatcher extends Catcher {
	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function fireEvent($input) {
		if (is_string($input)) {
			// If the input is a string
			$eventClass = $input;
			$eventName = $input;
	        if(!class_exists($eventClass)){
	            // Check if the file even exists
	            $file = "Core/Events/event.".$eventName.".php";
	            if(file_exists($file)){
	                // Load the file
	                require_once($file);
	            }else{
	                // No event arguments? Looks like a notify-event
	                if(func_num_args() == 1){
	                    // Load notify-event-class
	                    $eventClass = '\FuzeWorks\NotifierEvent';
	                }else{
	                    // No notify-event: we tried all we could
	                    throw new Exception("Event ".$eventName." could not be found!");
	                }
	            }
	        }

	        $event = new $eventClass($this);
		} elseif (is_object($input)) {
			$eventName = get_class($input);
			$eventName = explode('\\', $eventName);
			$eventName = end($eventName);
			$event = $input;			
		} else {
			// INVALID EVENT
			return false;
		}

		if (func_num_args() > 1)
			call_user_func_array(array($event, 'init'), array_slice(func_get_args(), 1));

		return $event;
	}
}


?>