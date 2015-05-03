<?php

use \FuzeWorks\Event;

/**
 * Class routerRouteEvent
 *
 * Fired after the router has extracted the path
 *
 * @package System\Events
 */
class routerRouteEvent extends Event{

    /**
     * @var array The routing table
     */
    public $routes;

    /**
     * @var boolean Whether the callable will be loaded directly after or not
     */
    public $loadCallable;

    public function init($routes, $loadCallable){

        $this->routes       = $routes;
        $this->loadCallable = $loadCallable;
    }
}