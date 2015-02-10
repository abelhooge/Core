<?php

abstract class Bus {
    protected $core;
    protected $mods;

    protected $library;
    protected $config;
    protected $logger;
    protected $models;
    protected $layout;
    protected $events;

    protected function __construct(&$core){
        $this->core = &$core;
        $this->mods = new ModHolder($this->core);

        $this->config           = &$core->mods->config;
        $this->logger           = &$core->mods->logger;
        $this->models           = &$core->mods->models;
        $this->layout           = &$core->mods->layout;
        $this->events           = &$core->mods->events;
    }

}

// Holds the mods in a seperate object besides the bus
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