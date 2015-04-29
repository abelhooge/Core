<?php

namespace FuzeWorks;

/**
 * Interface for a Module that gives abstract Controller types
 * A Controller server must contain the methods from this interface in order to correctly serve Controllers
 */
interface ControllerServer {
	public function giveController($type);
}

/**
 * Abstract class Controller
 * 
 * Abstract for a Controller data representation, loads the correct parent type
 */
abstract class Controller extends Bus{

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
	 * Set the type of this Controller. Eg, use techfuze/databaseController and DatabaseController to get a SQL connected Controller
	 * @access protected
	 * @param String Module_name, the name of the module where the Controller can be found
	 * @param String Controller_type, Controller type to return
	 */
	protected function setType($module_name, $controller_type) {
		$mod = $this->core->loadMod($module_name);
		$this->parentClass = $mod->giveController($controller_type);
	}

	/**
	 * Retrieves a value from the controller class
	 * @access public
	 * @param Any key
	 * @return Any value from the controller class
	 */
	public function __get($name) {
		return $this->parentClass->$name;
	}

	/**
	 * Sets a value in the controller class
	 * @access public
	 * @param Any key 
	 * @param Any value
	 */
	public function __set($name, $value) {
		$this->parentClass->$name = $value;
	}

	/**
	 * Calls a function in the controller class
	 * @access public
	 * @param String function_name
	 * @param Array values
	 * @return Function return
	 */
	public function __call($name, $params) {
		return call_user_func_array(array($this->parentClass, $name), $params);			
	}
}