<?php
/**
 * FuzeWorks
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

namespace FuzeWorks;

/**
 * Class Module
 *
 * Abstract Class for modules
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Module {

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
	 * @var array Advertisements send from other modules
	 */
	protected $advertisements = array();

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
			Logger::logWarning("Could not write module config. ModulePath is not set", get_class($this));
			return false;
		}

		if (file_exists($file) && is_writable($file)) {
			$config = var_export($this->cfg, true);
			file_put_contents($file, "<?php return $config ;");
		}
	}

	/**
	 * Set the advertisements send by other modules
	 * @param array $advertisements Advertisement data
	 */
	public function setAdvertisements($advertiseName, $advertiseData) {
		$this->advertisements[$advertiseName] = $advertiseData;
	}

	/**
	 * Get the advertisements with a specific name
	 * @param  String $advertiseName AdvertisementName
	 * @return array                 AdvertisementData
	 */
	public function getAdvertisements($advertiseName) {
		return $this->advertisements[$advertiseName];
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