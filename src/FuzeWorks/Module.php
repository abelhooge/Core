<?php
/**
 * FuzeWorks.
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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

namespace FuzeWorks;

/**
 * Trait Module.
 *
 * Contains all the methods modules should have
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
trait Module
{
    /**
     * @var null|string Relative path to the module
     */
    protected static $modulePath = null;

    /**
     * @var string Internal name of the module
     */
    protected static $moduleName = 'placeholder';

    /**
     * @var string name used in the mod array
     */
    protected static $linkName = 'placeholder';

    /**
     * @var moduleInfo object of the module
     */
    protected static $cfg;

    /**
     * @var array Advertisements send from other modules
     */
    protected static $advertisements = array();

    /**
     * Returns the name of the module.
     *
     * @return string Returns the name of the module
     */
    public static function getModuleName()
    {
        return self::$moduleName;
    }

    /**
     * Returns the path to the module.
     *
     * @return null|string
     */
    public static function getModulePath()
    {
        return self::$modulePath;
    }

    /**
     * Returns the config of the module (moduleInfo.php).
     *
     * @return stdClass module config
     */
    public static function getModuleConfig()
    {
        return self::$cfg;
    }

    /**
     * Changes the path to the location of the module.
     *
     * This function can only be executed once, because when the path has been set this function won't save changes anymore.
     * This prevents modules or other systems to mess with modules and their stability.
     *
     * @param string $modulePath
     */
    public static function setModulePath($modulePath = null)
    {
        // Only allow one change of this variable from outside
        if (self::$modulePath === null) {
            self::$modulePath = $modulePath;
        }
    }

    /**
     * Set the link name of the module. The link name is the address in the module array so that the module can self reference.
     *
     * @param string link name
     */
    public static function setModuleLinkName($linkName)
    {
        self::$linkName = $linkName;
    }

    /**
     * The name that is required to load itself, eg 'exampleauthor/examplemodulename' or 'techfuze/cms'.
     *
     * @param string module name
     */
    public static function setModuleName($modName)
    {
        self::$moduleName = $modName;
    }

    /**
     * Add the moduleInfo.php to the module for direct interaction.
     *
     * @param stdClass module config
     */
    public static function setModuleConfig($config)
    {
        self::$cfg = $config;
    }

    /**
     * Set a value in the modules moduleInfo.php.
     *
     * @param mixed config Key
     * @param mixed config value
     */
    public static function setConfigValue($key, $value)
    {
        $file = self::getModulePath().'moduleInfo.php';
        self::$cfg->$key = $value;

        // Check if the module path is set yet
        if (self::getModulePath() == null) {
            Logger::logWarning('Could not write module config. ModulePath is not set', get_class($this));

            return false;
        }

        if (file_exists($file) && is_writable($file)) {
            $config = var_export($this->cfg, true);
            file_put_contents($file, "<?php return $config ;");
        }
    }

    /**
     * Set the advertisements send by other modules.
     *
     * @param array $advertisements Advertisement data
     */
    public static function setAdvertisements($advertiseName, $advertiseData)
    {
        self::$advertisements[$advertiseName] = $advertiseData;
    }

    /**
     * Get the advertisements with a specific name.
     *
     * @param string $advertiseName AdvertisementName
     *
     * @return array AdvertisementData
     */
    public static function getAdvertisements($advertiseName)
    {
        return self::$advertisements[$advertiseName];
    }

    /**
     * Return a value from the module configuration.
     *
     * @param mixed config Key
     *
     * @return mixed config value
     */
    public static function getConfigValue($key)
    {
        return self::$cfg->$key;
    }
}
