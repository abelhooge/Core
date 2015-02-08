<?php
/**
 * @author FuzeNetwork
 * @package files
*/
 
/** 
 * @name Events
 * @package Core
*/
class Events extends Bus{

	private $listeners;

	public function __construct(&$core) {
		parent::__construct($core);
		$this->listeners = array();
	}

	## EVENTS
	public function fireEvent($input) {
		if (is_string($input)) {
			// STRING

			$eventClass = $input;
			$eventName = $input;
	        if(!class_exists($eventClass)){
	            // Check if the file even exists
	            $file = FUZEPATH . "/Core/Events/event.".$eventName.".php";
	            if(file_exists($file)){
	                // Load the file
	                require_once($file);
	            }else{
	                // No event arguments? Looks like an notify-event
	                if(func_num_args() == 1){
	                    // Load notify-event-class
	                    $eventClass = 'NotifierEvent';
	                }else{
	                    // No notify-event: we tried all we could
	                    throw new \Exception("Event ".$eventName." could not be found!");
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

		$this->logger->newLevel("Firing Event: '".$eventName."'");
		$this->logger->log('Initializing Event');

		if (func_num_args() > 1)
			call_user_func_array(array($event, 'init'), array_slice(func_get_args(), 1));

		$this->logger->log("Cycling Listeners");
		for ($i=0; $i < count($this->listeners); $i++) {
			$class = $this->listeners[$i];
			if (method_exists($class, 'on'.ucfirst($eventName))) {
				$result = $class->{'on'.ucfirst($eventName)}($event);
				$event = ($result !== null ? $result : $event);
			}
		}

		$this->logger->stopLevel();
		return $event;
	}

	public function addListener(&$object) {
		if (is_object($object)) {
			$this->listeners[] = $object;
		}
	}

}

class NotifierEvent extends Event {}


?>