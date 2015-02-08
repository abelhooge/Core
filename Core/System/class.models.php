<?php
/**
 * @author FuzeNetwork
 * @package files
 */

/** Config
 *
 * This class keeps the config loaded at all times via a singleton class
 *
 * @name Config
 * @package Core
 */
class Models extends Bus{
	
    private $models_array = array();
    private $model_types = array();

    public function __construct(&$core){
        parent::__construct($core);
    }

    public function loadModel($name, $directory = null){
        if($directory === null){
            $directory = FUZEPATH . "/Application/Models";
        }

        $file = $directory.'/model.'.$name.'.php';
        if (isset($this->model_types[$name])) {
            $this->logger->logInfo('MODEL LOAD: '.get_class($this->model_types[$name]), get_class($this->model_types[$name]), __FILE__, __LINE__);
            $this->models_array[$name] = $this->model_types[$name];
        } elseif (file_exists($file)){
            require_once($file);
            $model = ucfirst($name);
            $this->logger->logInfo('MODEL LOAD: '.$model, $model, __FILE__, __LINE__);
            $this->models_array[$name] = new $model($this->core);
        } else{
        	$this->logger->logWarning('The requested model: \''.$name.'\' could not be found. Loading empty model', 'FuzeWorks->Model');
            require_once(FUZEPATH . "/Core/System/Models/fz-model-interpret.php");
            $this->logger->logInfo('MODEL LOAD: interprated model', 'FuzeWorks->Model', __FILE__, __LINE__);
            $model = new Interpret($this->core);
            $model->table($name);
            $this->models_array[$name] = $model;
        }
    }

    public function register($NAME, $MODEL_OBJECT) {
        if (!isset($this->model_types[strtolower($NAME)])) {
            $this->model_types[strtolower($NAME)] = $MODEL_OBJECT;
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

    public function getEmptyModel() {
        return new \FuzeWorks\V100\DatabaseModel($this->core);
    }
}
 
?>