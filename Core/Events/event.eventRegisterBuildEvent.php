<?php

class EventRegisterBuildEvent extends Event {
	
	public $register = array();

    public function init($call){}

    public function addEvent($modName, $eventName) {
    	if (!isset($this->register[$eventName])) {
    		$this->register[$eventName] = array();
    	}
    	$this->register[$eventName][] = $modName;
    }
}

?>