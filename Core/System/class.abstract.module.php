<?php

/**
 * Class Module
 *
 * Abstract Class for modules
 *
 * @package System\Core
 */
class Module extends Bus {

	/**
	 * @var null|string Relative path to the module
	 */
	protected $modulePath = null;

	/**
	 * @var string Internal name of the module
	 */
	protected $moduleName = 'placeholder';

	/**
	 * Constructor
	 *
	 * @param Core $core Pointer to the core class
	 */
	public function __construct(&$core){
        parent::__construct($core);
    }

	/**
	 * Returns the name of the module
	 *
	 * @return string Returns the name of the module
	 */
	public function getModuleName(){

        return $this->moduleName;
    }

	/**
	 * Returns the path to the module
	 * @return null|string
	 */
	public function getModulePath(){

        return $this->modulePath;
    }

	/**
	 * Changes the path to the location of the module
	 *
	 * This function can only be executed once, because when the path has been set this function won't save changes anymore.
	 * This prevents modules or other systems to mess with modules and their stability.
	 *
	 * @param string $modulePath
	 */
	public function setModulePath($modulePath = null){

        // Only allow one change of this variable from outside
        if($this->modulePath === null)
            $this->modulePath = $modulePath;
    }
}