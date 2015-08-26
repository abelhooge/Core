<?php

namespace FuzeWorks;

/**
 * Class Bus
 *
 * Every class in this framework does somehow extend this class. Because it rocks.
 * This class offers a lot of pointers to important core classes, so every class can find other classes.
 *
 */
abstract class Bus {
    /**
     * @var \FuzeWorks\Core
     */
    protected $core;

    /**
     * @var \FuzeWorks\ModHolder
     */
    public $mods;

    /**
     * @var \FuzeWorks\Router
     */
    protected $router;

    /**
     * @var \FuzeWorks\Config
     */
    protected $config;

    /**
     * @var \FuzeWorks\Logger
     */
    protected $logger;

    /**
     * @var \FuzeWorks\Models
     */
    protected $models;

    /**
     * @var \FuzeWorks\Layout
     */
    protected $layout;

    /**
     * @var \FuzeWorks\Events
     */
    protected $events;

    /**
     * @var \FuzeWorks\Modules
     */
    protected $modules;

    /**
     * Create references to our core objects
     *
     * Because all of these variables are references, they are completely identical / always updated.
     * Any class that extends the CoreAbstract class now has access to the whole core.
     *
     * @param \FuzeWorks\Core $core
     */
    protected function __construct(&$core){
        $this->core = &$core;
        $this->mods = new ModHolder($this->core);

        $this->config           = &$core->mods->config;
        $this->logger           = &$core->mods->logger;
        $this->models           = &$core->mods->models;
        $this->layout           = &$core->mods->layout;
        $this->events           = &$core->mods->events;
        $this->router           = &$core->mods->router;
        $this->modules          = &$core->mods->modules;
    }

}

/**
 * An object class which holds modules so that other classes can access it.
 * This is used so that all classes that somehow extend Bus can access modules 
 * using $this->mods->MOD_NAME;
 */
class ModHolder {
    protected $core;
    public function __construct(&$core) {
        $this->core = &$core;
    }
    public function __get($name) {
        if (isset($this->core->mods->$name)) {
            return $this->core->mods->$name;
        } else {
            $this->core->loadMod($name);
            return $this->core->mods->$name;
        }
        
    }
}

?>