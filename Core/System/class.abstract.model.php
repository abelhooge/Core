<?php

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
	 * @param String class name, the class to load and connect to
	 */
	protected function setType($module_name, $class_name) {
		$this->core->loadMod($module_name);
		$this->parentClass = new $class_name($this->core);
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