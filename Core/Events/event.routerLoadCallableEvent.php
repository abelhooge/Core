<?php

use \FuzeWorks\Event;

/**
 * Class routerLoadCallableEvent
 *
 * Called when a callable is about to be loaded
 *
 * @package net.techfuze.fuzeworks.events
 */
class routerLoadCallableEvent extends Event{

    /**
     * @var callable The callable
     */
    public $callable;

    /**
     * @var null|string The controller-part of the route
     */
    public $controller = null;

    /**
     * @var null|string The function-part of the route
     */
    public $function   = null;

    /**
     * @var null|string The parameter-part of the route
     */
    public $parameters = null;

    public function init($callable, $controller, $function, $parameters){

        $this->callable   = $callable;
        $this->controller = $controller;
        $this->function   = $function;
        $this->parameters = $parameters;
    }
}