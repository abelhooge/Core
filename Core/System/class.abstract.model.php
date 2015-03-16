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
	 * @access public
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

	protected function setType($type) {
		
	}

	public function __get($name) {
		return $parentClass->$name;
	}

	public function __set($name, $value) {
		$parentClass->$name = $value;
	}

	public function __call($name, $params) {
		return call_user_func_array(array($this->parentClass, $name), $params);			
	}
}