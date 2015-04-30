<?php

namespace FuzeWorks;

class Event {

	private $cancelled = false;

	public function isCancelled() {
		return $this->cancelled;
	}

    public function setCancelled($cancelled) {
        if ($cancelled == true){
            $this->cancelled = true;
        } else{
            $this->cancelled = false;
        }
    }
}

class NotifierEvent extends Event {}

?>