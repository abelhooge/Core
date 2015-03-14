<?php
/**
 * @author FuzeNetwork
 * @package files
*/
 
/** 
 * @name Events
*/
class Events extends Bus{

	private $listeners;

	public function __construct(&$core) {
		parent::__construct($core);
		$this->listeners = array();
	}

    /**
     * Adds a function as listener
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param String $eventName The name of the event
     * @param int $priority The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     * @see EventPriority
     *
     * @throws EventException
     */
	public function addListener($callback, $eventName, $priority = EventPriority::NORMAL){
        if(EventPriority::getPriority($priority) == false)
            throw new Exception("Unknown priority " . $priority);

        if(!isset($this->listeners[$eventName]))
            $this->listeners[$eventName] = array();

        if(!isset($this->listeners[$eventName][$priority]))
            $this->listeners[$eventName][$priority] = array();

        $this->listeners[$eventName][$priority][] = $callback;
    }

    /**
     * Removes a function as listener
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param String $eventName The name of the event
     * @param int $priority The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     * @see EventPriority
     *
     * @throws EventException
     */
    public function removeListener($callback, $eventName, $priority = EventPriority::NORMAL){
        if(EventPriority::getPriority($priority) == false)
            throw new Exception("Unknown priority " . $priority);

        if(!isset($this->listeners[$eventName]))
            return;

        if(!isset($this->listeners[$eventName][$priority]))
            return;

        foreach($this->listeners[$eventName][$priority] as $i => $_callback){

            if($_callback == $callback) {
                unset($this->listeners[$eventName][$priority][$i]);
                return;
            }
        }
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

		$this->logger->log("Checking for Listeners");

        // Read the event register for listeners
        $register = $this->config->eventregister->register;
        if (isset($register[$eventName])) {
            for ($i=0; $i < count($register[$eventName]); $i++) { 
                $this->core->loadMod($register[$eventName][$i]);
            }
        }

        //There are listeners for this event
        if(isset($this->listeners[$eventName])) {
            //Loop from the highest priority to the lowest
            for ($priority = EventPriority::getHighestPriority(); $priority <= EventPriority::getLowestPriority(); $priority++) {
                //Check for listeners in this priority
                if (isset($this->listeners[$eventName][$priority])) {
                	$listeners = $this->listeners[$eventName][$priority];
                    $this->logger->newLevel('Found listeners with priority ' . EventPriority::getPriority($priority));
                    //Fire the event to each listener
                    foreach ($listeners as $callback) {
                        if(!is_string($callback[0]))
                            $this->logger->log('Firing ' . get_class($callback[0]) . '->' . $callback[1]);
                        else
                            $this->logger->log('Firing ' . join('->', $callback));
                        $this->logger->newLevel('');
                        try {
                            call_user_func($callback, $event);
                        } catch (ModuleException $e) {
                            $this->error->exceptionHandler($e);
                        }
                        $this->logger->stopLevel();
                    }

                    $this->logger->stopLevel();
                }
            }
        }


		$this->logger->stopLevel();
		return $event;
	}

    // Event Preparation:
    public function buildEventRegister() {
        $this->logger->newLevel("Building Event Register", 'Events');
        $dir = FUZEPATH . "/Core/Mods/";
        $mods = $this->config->modregister->register;
        foreach ($mods as $key => $value) {
            $this->core->loadMod($key);
        }

        $event = $this->fireEvent('eventRegisterBuildEvent', '');
        $this->config->set('eventregister', 'register', $event->register);

        $this->logger->stopLevel();
    }
}

class NotifierEvent extends Event {}


?>