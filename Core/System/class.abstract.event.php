<?php

namespace FuzeWorks;

/**
 * Class Event
 *
 * A simple class for events. The only current purpose is to be able to cancel events, but it can be easily extended.
 */
class Event {

	private $cancelled = false;

    /**
     * @return boolean True if the event is cancelled, false if the event is not cancelled
     */
	public function isCancelled() {
		return $this->cancelled;
	}

    /**
     * @param boolean $cancelled True if the event is cancelled, false if the event is not cancelled
     */
    public function setCancelled($cancelled) {
        if ($cancelled == true){
            $this->cancelled = true;
        } else{
            $this->cancelled = false;
        }
    }
}

/**
 * Simple event which will notify components of an event, but does not contain any data
 */
class NotifierEvent extends Event {}

?>