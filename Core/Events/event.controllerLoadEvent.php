<?php

use \FuzeWorks\Event;

class ControllerLoadEvent extends Event {
	
	public $route;
	public $controllerName;
	public $function;
	public $parameters;
	public $directory;

    public function init($route, $controllerName, $function, $parameters, $directory) {
		$this->route = $route;
		$this->controllerName = $controllerName;
		$this->function = $function;
		$this->parameters = $parameters;
		$this->directory = $directory;
    }
}

?>