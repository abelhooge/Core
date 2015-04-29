<?php

namespace FuzeWorks;

/**
 * Interface for a Module that gives abstract model types
 * A model server must contain the methods from this interface in order to correctly serve models
 */
interface ModelServer {
	public function giveModel($type);
}

/**
 * Abstract class Model
 * 
 * Abstract for a model data representation, loads the correct parent type
 */
abstract class Model extends Bus{

	/**
	 * The parent class holder object
	 * Requests get redirected to this class 
	 * @access private
	 * @var Parent Object
	 */
	private $parentClass;

	/**
	 * Constructs the class and Bus
	 * @access public
	 * @param Core Object, gets referenced 
	 */
	public function __construct(&$core) {
		parent::__construct($core);
	}

	/**
	 * Set the type of this model. Eg, use techfuze/databasemodel and Databasemodel to get a SQL connected model
	 * @access protected
	 * @param String Module_name, the name of the module where the model can be found
	 * @param String Model_type, model type to return
	 */
	protected function setType($module_name, $model_type) {
		$mod = $this->core->loadMod($module_name);
		$this->parentClass = $mod->giveModel($model_type);
	}

	/**
	 * Retrieves a value from the model class
	 * @access public
	 * @param Any key
	 * @return Any value from the model class
	 */
	public function __get($name) {
		return $this->parentClass->$name;
	}

	/**
	 * Sets a value in the model class
	 * @access public
	 * @param Any key 
	 * @param Any value
	 */
	public function __set($name, $value) {
		$this->parentClass->$name = $value;
	}

	/**
	 * Calls a function in the model class
	 * @access public
	 * @param String function_name
	 * @param Array values
	 * @return Function return
	 */
	public function __call($name, $params) {
		return call_user_func_array(array($this->parentClass, $name), $params);			
	}
}