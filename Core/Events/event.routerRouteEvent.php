<?php

class RouterRouteEvent extends Event {
	
	public $controller;
	public $function;
	public $parameters;
    public $directory;

    public function init($controller, $function, $parameters){
        $this->controller   = $controller;
        $this->function     = $function;
        $this->parameters   = $parameters;
    }
}

?>