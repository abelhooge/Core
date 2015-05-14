<?php

namespace FuzeWorks;

/**
 * Class Module
 *
 * Abstract Class for modules
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
	 * @var String name used in the mod array 
	 */
	protected $linkName = 'placeholder';

	/**
	 * @var moduleInfo object of the module
	 */
	protected $cfg;

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
     * Returns the config of the module (moduleInfo.php)
     * @access public
 	 * @return stdClass module config
     */
    public function getModuleConfig() {
    	return $this->cfg;
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

    /**
     * Set the link name of the module. The link name is the address in the module array so that the module can self reference.
     * @access public
     * @param String link name
     */
    public function setModuleLinkName($linkName) {
    	$this->linkName = $linkName;
    }

    /** 
     * The name that is required to load itself, eg 'exampleauthor/examplemodulename' or 'techfuze/cms'
     * @access public
     * @param String module name
     */
    public function setModuleName($modName) {
    	$this->moduleName = $modName;
    }

    /**
     * Add the moduleInfo.php to the module for direct interaction
     * @access public
     * @param stdClass module config
     */
    public function setModuleConfig($config) {
    	$this->cfg = $config;
    }

    /**
     * Set a value in the modules moduleInfo.php
     * @access protected
     * @param Mixed config Key
     * @param Mixed config value
     */
	public function setConfigValue($key, $value) {
		$file = $this->getModulePath() . "moduleInfo.php";
		$this->cfg->$key = $value;

		// Check if the module path is set yet
		if ($this->getModulePath() == null) {
			$this->logger->logWarning("Could not write module config. ModulePath is not set", get_class($this));
			return false;
		}

		if (file_exists($file) && is_writable($file)) {
			$config = var_export($this->cfg, true);
			file_put_contents($file, "<?php return $config ;");
		}
	}

	/**
	 * Return a value from the module configuration
	 * @access public
	 * @param Mixed config Key
	 * @return Mixed config value
	 */
	public function getConfigValue($key) {
		return $this->cfg->$key;
	}
}