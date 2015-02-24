<?php

class RouterRouteEvent extends Event {
	
	public $controller;
	public $function;
	public $parameters;
	public $directory;

    public function init($controller, $function, $parameters, $directory){
        $this->controller   = $controller;
        $this->function     = $function;
        $this->parameters   = $parameters;
        $this->directory 	= $directory;
    }
}

?>