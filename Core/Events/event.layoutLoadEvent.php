<?php

use \FuzeWorks\Event;

class LayoutLoadEvent extends Event {
	
    public $directory;
    public $layout;

    public function init($layout){
        $this->layout = $layout;
    }
}

?>