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

use stdClass;

/**
 * Modules Class.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Modules
{
    /**
     * A register of all the existing module headers.
     *
     * The module headers contain information required to loading the module
     *
     * @var array
     */
    private static $register;

    /**
     * A register which holds all the module advertisements by key.
     *
     * @var array
     */
    private static $advertiseRegister = array();

    /**
     * An array of all the loaded modules.
     *
     * @var array
     */
    public static $modules = array();

    /**
     * An array with the names of all modules that are loaded, and should not be loaded again.
     *
     * @var array of module names
     */
    private static $loaded_modules = array();

    /**
     * An array which holds the routes to module to load them quickly.
     *
     * @var array
     */
    private static $module_routes = array();

    /**
     * Retrieves a module and returns it.
     * If a module is already loaded, it returns a reference to the loaded version.
     *
     * @param string $name Name of the module
     *
     * @return \FuzeWorks\Module Module The module
     *
     * @throws \FuzeWorks\ModuleException
     */
    public static function get($name)
    {
        // Where the modules are
        $path = 'Modules/';

        // Check if the requested module is registered
        if (isset(self::$register[$name])) {
            if (!empty(self::$register[$name])) {
                // Load the moduleInfo
                $cfg = (object) self::$register[$name];

                // Check if the module is disabled
                if (isset($cfg->meta)) {
                    throw new ModuleException("Could not load module. Module '".$name."' is not enabled", 1);
                }

                // Check if the module is already loaded. If so, only return a reference, if not, load the module
                if (in_array(strtolower($name), self::$loaded_modules)) {
                    // return the link
                    $c = self::$modules[strtolower($cfg->module_name)];

                    return $c;
                } else {
                    // Load the module
                    $file = $cfg->directory.'/'.$cfg->module_file;

                    // Load the dependencies before the module loads
                    $deps = (isset($cfg->dependencies) ? $cfg->dependencies : array());
                    for ($i = 0; $i < count($deps); ++$i) {
                        self::get($deps[$i]);
                    }

                    // Check if the file exists
                    if (file_exists($file)) {
                        // And load it
                        include_once $file;
                        $class_name = $cfg->module_class;
                        $msg = "Loading Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name))."'";
                        $msg .= (isset($cfg->version) ? '; version: '.$cfg->version : '');
                        $msg .= (isset($cfg->author) ? '; made by '.$cfg->author : '');
                        $msg .= (isset($cfg->website) ? '; from '.$cfg->website : '');
                        Logger::log($msg);
                    } else {
                        // Throw Exception if the file does not exist
                        throw new ModuleException("Could not load module. Module '".$name."' class file was not found.", 1);

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
                        $CLASS::setModulePath($cfg->directory);
                    }

                    if (method_exists($CLASS, 'setModuleLinkName')) {
                        $CLASS::setModuleLinkName(strtolower($cfg->module_name));
                    }

                    if (method_exists($CLASS, 'setModuleName')) {
                        $CLASS::setModuleName($name);
                    }

                    // Send all advertisements
                    if (isset($cfg->listenFor)) {
                        $listenFor = $cfg->listenFor;
                        if (method_exists($CLASS, 'setAdvertisements')) {
                            foreach ($listenFor as $advertiseName) {
                                if (isset(self::$advertiseRegister[$advertiseName])) {
                                    $CLASS::setAdvertisements($advertiseName, self::$advertiseRegister[$advertiseName]);
                                }
                            }
                        } else {
                            throw new ModuleException("Could not load module. Module '".$name."' listens for advertisement but does not implement setAdvertisements() method.", 1);
                        }
                    }

                    // Send the moduleConfig if possible
                    if (method_exists($CLASS, 'setModuleConfig')) {
                        // Append the config file to the module CFG (accessable through $this->cfg)
                        if (file_exists($cfg->directory.'/'.'config.'.strtolower($cfg->module_name).'.php')) {
                            $data = (object) include $cfg->directory.'/'.'config.'.strtolower($cfg->module_name).'.php';
                            foreach ($data as $key => $value) {
                                $cfg->$key = $value;
                            }
                        }

                        $CLASS::setModuleConfig($cfg);
                    }

                    // And finally check if it can be loaded
                    if (!method_exists($CLASS, 'onLoad')) {
                        throw new ModuleException("Could not load module. Module '".$name."' does not have an onLoad() method", 1);
                    }

                    // Prepare onLoad call
                    $args = func_get_args();
                    array_shift($args);

                    // And call onLoad
                    call_user_func_array(array($CLASS, 'onLoad'), $args);

                    // Add to the loaded modules
                    self::$loaded_modules[] = strtolower($name);

                    // Return a reference
                    return self::$modules[strtolower($cfg->module_name)] = &$CLASS;
                }
            } else {
                throw new ModuleException("Could not load module. Module '".$name."' has an invalid config", 1);
            }
        } else {
            throw new ModuleException("Module not found: '".$name."'", 1);
        }
    }

    /**
     * Set the value of a module config or moduleInfo.php.
     *
     * @param string $file  File to edit
     * @param string $key   Key to edit
     * @param mixed  $value Value to set
     */
    private static function setModuleValue($file, $key, $value)
    {
        if (file_exists($file) && is_writable($file)) {
            $cfg = include $file;
            $cfg[$key] = $value;
            $config = var_export($cfg, true);
            file_put_contents($file, "<?php return $config ;");
        }
    }

    /**
     * Add a module using a moduleInfo.php file.
     *
     * @param string   Path to moduleInfo.php file
     *
     * @throws FuzeWorks\ModuleException
     */
    public static function addModule($moduleInfo_file)
    {
        $file = $moduleInfo_file;
        $directory = dirname($file);
        if (file_exists($file)) {
            $cfg = (object) include $file;
            $cfg->directory = $directory;

            // Define the module name
            $name = '';
            $name .= (!empty($cfg->author) ? strtolower($cfg->author).'/' : '');
            $name .= strtolower($cfg->module_name);

            Logger::log("Adding module: '".$name."'");
            if (isset(self::$register[$name])) {
                Logger::logError("Module '".$name."' can not be added. Module is already loaded");

                return false;
            }

            // Check whether the module is enabled or no
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
                    self::$register[$name] = (array) $cfg2;
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
     * Enables a module when it is disabled.
     *
     * @param string    Module name
     * @param bool   true for permanent enable
     *
     * @throws FuzeWorks\ModuleException
     */
    public static function enableModule($name, $permanent = true)
    {
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
            self::$register[$name] = (array) $info;

            Logger::log("Enabled module '".$name."'");

            // Enable it permanently if so desired
            if ($permanent) {
                $file = $info->directory.'/moduleInfo.php';
                self::setModuleValue($file, 'enabled', true);
            }

            // Reload the eventRegister
            Events::buildEventRegister();
        } else {
            throw new ModuleException("Could not enable module '".$name."'. Module does not exist.", 1);
        }
    }

    /**
     * Disableds a module when it is enabled.
     *
     * @param string    Module name
     * @param bool   true for permanent disable
     *
     * @throws FuzeWorks\ModuleException
     */
    public static function disableModule($name, $permanent = true)
    {
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

            self::$register[$name] = (array) $disabled;
            Logger::log("Disabled module '".$name."'");
            if ($permanent) {
                $file = $info->directory.'/moduleInfo.php';
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

    /**
     * Create a register with all the module headers from all the existing modules.
     *
     * Used to correctly load all modules
     * @param   bool    $cache          true if loading from cache
     * @param   string  $cachingMethod  the driver used in the caching library
     * @param   int     $cachingTime    The time the registers are cached
     */
    public static function buildRegister($cache = false, $cachingMethod = 'file', $cachingTime = 300)
    {
        // First check if the caching engine can be used
        if ($cache)
        {   
            // Retrieve the cache if possible
            $cache = Factory::getInstance()->libraries->getDriver('cache');
            $cacheData = $cache->$cachingMethod->get('moduleRegisters');

            if ( ! is_bool($cacheData) )
            {
                // Separate the data
                $moduleRegister = $cacheData['moduleRegister'];
                $eventRegister = $cacheData['eventRegister'];
                $routeRegister = $cacheData['routeRegister'];
                $advertiseRegister = $cacheData['advertiseRegister'];

                // And register it all
                Logger::newLevel("Loadig Module Headers from Cache");
                self::$register = $moduleRegister;
                Events::$register = $eventRegister;
                self::$advertiseRegister = $advertiseRegister;
                self::$module_routes = $routeRegister;
                foreach ($routeRegister as $route => $name) {
                    Factory::getInstance()->router->addRoute($route, array('callable' => array('\FuzeWorks\Modules', 'moduleCallable')), true);
                }
                Logger::stopLevel();
                return true;
            }
        }

        Logger::newLevel('Loading Module Headers', 'Core');

        // Get all the module directories
        $dir = 'Modules/';
        $mod_dirs = array();
        $mod_dirs = array_values(array_diff(scandir($dir), array('..', '.')));

        // Build the module and event register
        $register = array();
        $event_register = array();

        // Cycle through all module directories
        for ($i = 0; $i < count($mod_dirs); ++$i) {
            $mod_dir = $dir.$mod_dirs[$i].'/';
            // If a moduleInfo.php exists, load it
            if (file_exists($mod_dir.'/moduleInfo.php')) {
                // Load the configuration file
                $cfg = (object) include $mod_dir.'/moduleInfo.php';

                // Set enabled for now
                $enabled = true;

                // Define the module name
                $name = '';
                $name .= (!empty($cfg->author) ? strtolower($cfg->author).'/' : '');
                $name .= strtolower($cfg->module_name);

                // Get the module directory
                $cfg->directory = $mod_dir;

                // Check whether the module is disabled
                if (isset($cfg->enabled)) {
                    if (!$cfg->enabled) {
                        // If disabled, set the variable
                        $enabled = false;

                        // If disabled, a holder will be placed so it might be enabled in the future
                        $mock = new StdClass();
                        $mock->module_name = $cfg->module_name;
                        $mock->directory = $cfg->directory;
                        $mock->meta = $cfg;
                        $mock->aliases = $cfg->aliases;

                        // Important, change the configuration to the mock, so we can duplicate it afterwards
                        $cfg = $mock;
                    }
                }

                // Copy all the data into the register and enable
                $register[$name] = (array) $cfg;

                // Log the name for enabled and disabled
                if (!$enabled) {
                    Logger::newLevel("[OFF] '".$name."'");
                } else {
                    Logger::newLevel("[ON]  '".$name."'");
                }

                // And possibly some aliases
                if (isset($cfg->aliases)) {
                    foreach ($cfg->aliases as $alias) {
                        $register[$alias] = (array) $cfg;
                        unset($register[$alias]['events']);
                        Logger::log("&nbsp;&nbsp;&nbsp;'".$alias."' (alias of '".$name."')");
                    }
                }

                // If not enabled, log it, wrap it and off to the next one
                if (!$enabled) {
                    Logger::stopLevel();
                    continue;
                }

                // Otherwise continue and add routing paths
                if (isset($cfg->routes)) {
                    // Get routes and add them
                    foreach ($cfg->routes as $route) {
                        // Create the route and callable and parse them
                        $callable = array('\FuzeWorks\Modules', 'moduleCallable');
                        Factory::getInstance()->router->addRoute($route, array('callable' => $callable), true);
                        self::$module_routes[$route] = $name;
                    }
                }

                // And for the events
                if (isset($cfg->events)) {
                    // Get the events and add them
                    foreach ($cfg->events as $event) {
                        // First check if the event already exists, if so, append it
                        if (isset($event_register[$event])) {
                            $event_register[$event][] = $name;
                        } else {
                            $event_register[$event] = array($name);
                        }

                        // Log the event
                        Logger::Log('Event added: \''.$event.'\'');
                    }
                }

                // And check for an advertisement tag
                if (isset($cfg->advertise)) {
                    // Cycle through advertisements
                    foreach ($cfg->advertise as $advertiseName => $advertiseData) {
                        // Log advertisement
                        Logger::log('Advertisement added: \''.$advertiseName.'\'');

                        // Add to advertiseRegister
                        self::$advertiseRegister[$advertiseName][$name] = $advertiseData;
                    }
                }

                Logger::stopLevel();
            } else {
                // If no details are specified, create a basic mock module
                $name = $mod_dirs[$i];

                // Build a default mock module config
                $mock = new stdClass();
                $mock->module_class = ucfirst($name);
                $mock->module_file = 'class.'.strtolower($name).'.php';
                $mock->module_name = $name;
                $mock->dependencies = array();
                $mock->versions = array();
                $mock->directory = $mod_dir;

                // Apply it
                $register[$name] = (array) $mock;
                Logger::newLevel("[ON]  '".$name."'");
                Logger::stopLevel();
            }
        }

        if ($cache)
        {
            Logger::log("Saving registry to cache");
            $cacheData = array(
                    'moduleRegister' => $register,
                    'eventRegister' => $event_register,
                    'routeRegister' => self::$module_routes,
                    'advertiseRegister' => self::$advertiseRegister
                );
            $cache->$cachingMethod->save('moduleRegisters', $cacheData, $cachingTime);
        }

        // And apply the registers to their dedicate location
        self::$register = $register;
        Events::$register = $event_register;
        Logger::stopLevel();
    }

    /**
     * The Module Callable.
     *
     * When a module listens for a specific routing path, this callable get's called.
     * After this the module can handle the request with the route() function in the module's root directory
     *
     * @param array   Regex matches
     */
    public static function moduleCallable($matches = array())
    {
        // First detect what module is attached to this route
        Logger::newLevel('Module callable called!');

        // Get the route
        $route = !empty($matches['route']) ? $matches['route'] : null;

        // See if the route exists
        if (isset(self::$module_routes[$route])) {
            Logger::log("Module '".self::$module_routes[$route]."' matched given route");

            // Load the module
            $mod = self::get(self::$module_routes[$route]);
            unset($matches['route']);
            $mod->route($matches);
        } else {
            Logger::logError('Route did not match known module. Fatal error');

            return Logger::http_error(500);
        }

        Logger::stopLevel();
    }
}
