<?php
/**
 * @author FuzeNetwork
 */

namespace FuzeWorks;

/**
 * Models Class
 * 
 * Simple loader class for MVC Models. Typically loads models from Application/Models unless otherwise specified.
 * If a model is not found, it will load a DatabaseModel type which will analyze the database and can directly be used. 
 */
class Models extends Bus{
	
    private $models_array = array();
    private $model_types = array();
    private $models_loaded = false;

    public function __construct(&$core){
        parent::__construct($core);
    }

    public function loadModel($name, $directory = null){
        // Model load event
        $event = $this->events->fireEvent('modelLoadEvent', $name);
        $directory          = ($event->directory === null ? FUZEPATH . "/Application/Models" : $event->directory);
        $name               = ($event->model === null ? $name : $event->model);

        $file = $directory.'/model.'.$name.'.php';
        if (isset($this->model_types[$name])) {
            $this->logger->logInfo('Loading Model: '.get_class($this->model_types[$name]), get_class($this->model_types[$name]));
            $this->models_array[$name] = $this->model_types[$name];
        } elseif (file_exists($file)){
            require_once($file);
            $model = "\Model\\" . ucfirst($name);
            $this->logger->logInfo('Loading Model: '.$model, $model);
            $this->models_array[$name] = new $model($this->core);
        } else{
        	$this->logger->logWarning('The requested model: \''.$name.'\' could not be found. Loading empty model', 'Models');
            require_once(FUZEPATH . "/Core/System/Models/model.interpret.php");
            $this->logger->logInfo('Loading Model: interprated databasemodel', 'Models');
            $model = new Interpret($this->core);
            $model->table($name);
            $this->models_array[$name] = $model;
        }
    }

    public function __get($name){
    	if (isset($this->models_array[strtolower($name)])) {
    		return $this->models_array[strtolower($name)];
    	} else {
    		$this->loadModel(strtolower($name));
    		return $this->models_array[strtolower($name)];
    	}
    }
}
 
?>