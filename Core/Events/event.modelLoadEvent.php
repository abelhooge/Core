<?php

class ModelLoadEvent extends Event {
	
    public $directory;
    public $model;

    public function init($model){
        $this->model = $model;
    }
}

?>