<?php

use \FuzeWorks\Event;

class ModelLoadEvent extends Event {
	
    public $directory;
    public $model;

    public function init($model, $directory){
        $this->model = $model;
        $this->directory = $directory;
    }
}

?>