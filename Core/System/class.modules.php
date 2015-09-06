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
use \stdClass;

/**
 * Modules Class
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Modules {

    public static $register;
    public static $modules = array();

    /**
     * An array which modules are loaded, and should not be loaded again
     * @access private
     * @var Array of module names
     */
    private static $loaded_modules = array();

    /**
     * Retrieves a module and returns it.
     * If a module is already loaded, it returns a reference to the loaded version
     * @param  String $name Name of the module
     * @return \FuzeWorks\Module Module The module
     * @throws FuzeWorks\ModuleException
     */
    public static function get($name) {
        // Where the modules are
        $path = "Modules/";

        // Check if the requested module is registered
        if (isset(self::$register[$name])) {
            if (!empty(self::$register[$name])) {
                // Load the moduleInfo
                $cfg = (object) self::$register[$name];

                // Check if the module is disabled
                if (isset($cfg->meta)) {
                    throw new ModuleException("Requested mod '".$name."' could not be loaded. Not enabled", 1);
                    return false;
                }

                // Check if the module is already loaded. If so, only return a reference, if not, load the module
                if (in_array($name, self::$loaded_modules)) {
                    // return the link
                    $msg = "Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name)) . "' is already loaded";
                    Logger::log($msg);
                    $c = self::$modules[strtolower($cfg->module_name)];
                    return $c;
                } else {
                    // Load the module
                    $file = $cfg->directory ."/". $cfg->module_file;

                    // Load the dependencies before the module loads
                    $deps = (isset($cfg->dependencies) ? $cfg->dependencies : array());
                    for ($i=0; $i < count($deps); $i++) {
                        self::get($deps[$i]);
                    }

                    // Check if the file exists
                    if (file_exists($file)) {
                        // And load it
                        require_once($file);
                        $class_name = $cfg->module_class;
                        $msg = "Loading Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name)) . "'";
                        $msg .= (isset($cfg->version) ? "; version: ".$cfg->version : "");
                        $msg .= (isset($cfg->author) ? "; made by ".$cfg->author : "");
                        $msg .= (isset($cfg->website) ? "; from ".$cfg->website: "");
                        Logger::log($msg);
                    } else {
                        // Throw Exception if the file does not exist
                        throw new ModuleException("Requested mod '".$name."' could not be loaded. Class file not found", 1);
                        return false;
                    }

                    // If it is an abstract module, load an StdClass for the module address
                    if (isset($cfg->abstract)) {
                        if ($cfg->abstract) {
                            $CLASS = new stdClass();
                            return self::$modules[strtolower($cfg->module_name)] = &$CLASS;
                        }
                    }

                    // Load the module class
                    $class_name = $cfg->module_class;
                    $CLASS = new $class_name();

                    // Apply default methods
                    if (method_exists($CLASS, 'setModulePath')) {
                        $CLASS->setModulePath($cfg->directory);
                    }
                    if (method_exists($CLASS, 'setModuleLinkName')) {
                        $CLASS->setModuleLinkName(strtolower($cfg->module_name));
                    }
                    if (method_exists($CLASS, 'setModuleName')) {
                        $CLASS->setModuleName($name);
                    }
                    if (method_exists($CLASS, 'setModuleConfig')) {
                        // Append the config file to the module CFG (accessable through $this->cfg)
                        if (file_exists($cfg->directory . "/" . "config.".strtolower($cfg->module_name).".php")) {
                            $data = (object) include($cfg->directory . "/" . "config.".strtolower($cfg->module_name).".php");
                            foreach ($data as $key => $value) {
                                $cfg->$key = $value;
                            }
                        }

                        $CLASS->setModuleConfig($cfg);
                    }

                    if (!method_exists($CLASS, 'onLoad')) {
                        throw new ModuleException("Module '".$name."' does not have an onLoad() method! Invalid module", 1);
                    }
                    $CLASS->onLoad();

                    // Add to the loaded modules
                    self::$loaded_modules[] = $name;

                    // Return a reference
                    return self::$modules[strtolower($cfg->module_name)] = &$CLASS;
                }
            }
        }
    }

    private static function setModuleValue($file, $key, $value) {
        if (file_exists($file) && is_writable($file)) {
            $cfg = require($file);
            $cfg[$key] = $value;
            $config = var_export($cfg, true);
            file_put_contents($file, "<?php return $config ;");
        }
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public static function addModule($moduleInfo_file) {
        $file = $moduleInfo_file;
        $directory = dirname($file);
        if (file_exists($file)) {
            $cfg = (object) require($file);
            $cfg->directory = $directory;

            // Define the module name
            $name = "";
            $name .= (!empty($cfg->author) ? strtolower($cfg->author)."/" : "");
            $name .= strtolower($cfg->module_name);

            Logger::log("Adding module: '".$name."'");
            if (isset(self::$register[$name])) {
                Logger::logError("Module '".$name."' can not be added. Module is already loaded");
                return false;
            }

            // Check wether the module is enabled or no
            if (isset($cfg->enabled)) {
                if ($cfg->enabled) {
                    // Copy all the data into the register and enable
                    self::$register[$name] = (array) $cfg;
                    Logger::log("[ON]  '".$name."'");
                } else {
                    // If not, copy all the basic data so that it can be enabled in the future
                    $cfg2 = new StdClass();
                    $cfg2->module_name = $cfg->module_name;
                    $cfg2->directory = $cfg->directory;
                    $cfg2->meta = $cfg;
                    self::$register[$name] = (array)$cfg2;
                    Logger::log("[OFF] '".$name."'");
                }
            } else {
                // Copy all the data into the register and enable
                self::$register[$name] = (array) $cfg;
                Logger::log("[ON]  '".$name."'");
            }
        } else {
            throw new ModuleException("Could not add module. '$moduleInfo_file' does not exist", 1);
        }
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public static function enableModule($name, $permanent = true) {
        if (isset(self::$register[$name])) {
            // Change the register
            $info = (object) self::$register[$name];

            // Do nothing if it is already enabled
            if (isset($info->enabled)) {
                if ($info->enabled) {
                    Logger::logWarning("Could not enable module '".$name."'. Module is already enabled.");
                    return false;
                }
            }

            // Otherwise move data from meta to the module config
            $info = $info->meta;
            $info->enabled = true;
            self::$register[$name] = (array)$info;

            Logger::log("Enabled module '".$name."'");

            // Enable it permanently if so desired
            if ($permanent) {
                $file = $info->directory . "/moduleInfo.php";
                self::setModuleValue($file, 'enabled', true);
            }

            // Reload the eventRegister
            Events::buildEventRegister();
        } else {
            throw new ModuleException("Could not enable module '".$name."'. Module does not exist.", 1);
        }
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public static function disableModule($name, $permanent = true) {
        if (isset(self::$register[$name])) {
            $info = (object) self::$register[$name];

            // Do nothing if it is already disabled
            if (isset($info->meta)) {
                Logger::logWarning("Could not disable module '".$name."'. Module is already disabled.");
                return false;
            }

            $disabled = new StdClass();
            $disabled->meta = $info;
            $disabled->directory = $info->directory;
            $disabled->module_name = $info->module_name;

            self::$register[$name] = (array)$disabled;
            Logger::log("Disabled module '".$name."'");
            if ($permanent) {
                $file = $info->directory . "/moduleInfo.php";
                self::setModuleValue($file, 'enabled', false);
            }

            // Reload the eventRegister
            Events::buildEventRegister();

            // Remove the existence of the module
            unset(self::$modules[strtolower($cfg->module_name)]);
        } else {
            throw new ModuleException("Could not disable module '".$name."'. Module does not exist.", 1);
        }

    }

    public static function buildRegister() {
        Logger::newLevel("Loading Module Headers", 'Core');

        // Get all the module directories
        $dir = "Modules/";
        $mod_dirs = array();
        $mod_dirs = array_values(array_diff(scandir($dir), array('..', '.')));

        // Build the module register
        $register = array();
        for ($i=0; $i < count($mod_dirs); $i++) {
            $mod_dir = $dir . $mod_dirs[$i] . "/";
            // If a moduleInfo.php exists, load it
            if (file_exists($mod_dir . "/moduleInfo.php")) {
                // Load the configuration file
                $cfg = (object) require($mod_dir . "/moduleInfo.php");

                // Define the module name
                $name = "";
                $name .= (!empty($cfg->author) ? strtolower($cfg->author)."/" : "");
                $name .= strtolower($cfg->module_name);

                // Get the module directory
                $cfg->directory = $mod_dir;

                // Check wether the module is enabled or no
                if (isset($cfg->enabled)) {
                    if ($cfg->enabled) {
                        // Copy all the data into the register and enable
                        $register[$name] = (array) $cfg;
                        Logger::log("[ON]  '".$name."'");

                        // Add all module aliases if available
                        if (isset($cfg->aliases)) {
                            foreach ($cfg->aliases as $alias) {
                                $register[$alias] = (array) $cfg;
                                unset($register[$alias]['events']);
                                Logger::log("&nbsp;&nbsp;&nbsp;'".$alias."' (alias of '".$name."')");
                            }
                        }
                    } else {
                        // If not, copy all the basic data so that it can be enabled in the future
                        $cfg2 = new StdClass();
                        $cfg2->module_name = $cfg->module_name;
                        $cfg2->directory = $cfg->directory;
                        $cfg2->meta = $cfg;
                        $register[$name] = (array)$cfg2;
                        Logger::log("[OFF] '".$name."'");

                        // Add all module aliases if available
                        if (isset($cfg->aliases)) {
                            foreach ($cfg->aliases as $alias) {
                                $register[$alias] = (array) $cfg2;
                                unset($register[$alias]['events']);
                                Logger::log("&nbsp;&nbsp;&nbsp;'".$alias."' (alias of '".$name."')");
                            }
                        }
                    }
                } else {
                    // Copy all the data into the register and enable
                    $register[$name] = (array) $cfg;
                    Logger::log("[ON]  '".$name."'");

                    // Add all module aliases if available
                    if (isset($cfg->aliases)) {
                        foreach ($cfg->aliases as $alias) {
                            $register[$alias] = (array) $cfg;
                            unset($register[$alias]['events']);
                            Logger::log("&nbsp;&nbsp;&nbsp;'".$alias."' (alias of '".$name."')");
                        }
                    }
                }
            } else {
                // If no details are specified, create a basic module
                $name = $mod_dirs[$i];

                // Build a default module config
                $cfg = new stdClass();
                $cfg->module_class = ucfirst($name);
                $cfg->module_file = 'class.'.strtolower($name).".php";
                $cfg->module_name = $name;
                $cfg->dependencies = array();
                $cfg->versions = array();
                $cfg->directory = $mod_dir;

                // Apply it
                $register[$name] = (array)$cfg;
                Logger::log("[ON]  '".$name."'");
            }
        }

        self::$register = $register;
        Logger::stopLevel();

    }

}

?>