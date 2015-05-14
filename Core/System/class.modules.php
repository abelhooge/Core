<?php
/**
 * @author FuzeNetwork
 */

namespace FuzeWorks;
use \stdClass;

/**
 * Modules Class
 * 
 */
class Modules extends Bus{

    public $register;
    public $modules;

    /**
     * An array which modules are loaded, and should not be loaded again
     * @access private
     * @var Array of module names
     */
    private $loaded_modules = array();

    public function __construct(&$core){
        parent::__construct($core);
        $this->modules = array();
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public function loadMod($name) {
        // Where the modules are
        $path = "Modules/";

        // Check if the requested module is registered
        if (isset($this->register[$name])) {
            if (!empty($this->register[$name])) {
                // Load the moduleInfo
                $cfg = (object) $this->register[$name];

                // Check if the module is disabled
                if (isset($cfg->meta)) {
                    throw new ModuleException("Requested mod '".$name."' could not be loaded. Not enabled", 1);
                    return false;   
                }

                // Check if the module is already loaded. If so, only return a reference, if not, load the module
                if (in_array($name, $this->loaded_modules)) {
                    // return the link
                    $msg = "Module '".ucfirst((isset($cfg->name) ? $cfg->name : $cfg->module_name)) . "' is already loaded";
                    $this->logger->log($msg);
                    $c = &$this->core->mods->{strtolower($cfg->module_name)};
                    return $c;
                } else {
                    // Load the module
                    $file = $cfg->directory ."/". $cfg->module_file;

                    // Load the dependencies before the module loads
                    $deps = (isset($cfg->dependencies) ? $cfg->dependencies : array());
                    for ($i=0; $i < count($deps); $i++) { 
                        $this->loadMod($deps[$i]);
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
                        $this->logger->log($msg);
                    } else {
                        // Throw Exception if the file does not exist
                        throw new ModuleException("Requested mod '".$name."' could not be loaded. Class file not found", 1);
                        return false;                           
                    }

                    // If it is an abstract module, load an StdClass for the module address
                    if (isset($cfg->abstract)) {
                        if ($cfg->abstract) {
                            $CLASS = new stdClass();
                            return $this->core->mods->{strtolower($cfg->module_name)} = &$CLASS;
                        }
                    }

                    // Load the module class
                    $class_name = $cfg->module_class;
                    $CLASS = new $class_name($this);

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
                        $CLASS->setModuleConfig($cfg);
                    }

                    if (!method_exists($CLASS, 'onLoad')) {
                        throw new ModuleException("Module '".$name."' does not have an onLoad() method! Invalid module", 1);
                    }
                    $CLASS->onLoad();

                    // Add to the loaded modules
                    $this->loaded_modules[] = $name;

                    // Return a reference
                    return $this->core->mods->{strtolower($cfg->module_name)} = &$CLASS;
                }
            }
        }
    }

    private function setModuleValue($file, $key, $value) {
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
    public function addModule($moduleInfo_file) {
        $file = $moduleInfo_file;
        $directory = dirname($file);
        if (file_exists($file)) {
            $cfg = (object) require($file);
            $cfg->directory = $directory;

            // Define the module name
            $name = "";
            $name .= (!empty($cfg->author) ? strtolower($cfg->author)."/" : "");
            $name .= strtolower($cfg->module_name);

            $this->logger->log("Adding module: '".$name."'");
            if (isset($this->register[$name])) {
                $this->logger->logError("Module '".$name."' can not be added. Module is already loaded");
                return false;
            }

            // Check wether the module is enabled or no
            if (isset($cfg->enabled)) {
                if ($cfg->enabled) {
                    // Copy all the data into the register and enable
                    $this->register[$name] = (array) $cfg;
                    $this->logger->log("[ON]  '".$name."'");
                } else {
                    // If not, copy all the basic data so that it can be enabled in the future
                    $cfg2 = new StdClass();
                    $cfg2->module_name = $cfg->module_name;
                    $cfg2->directory = $cfg->directory;
                    $cfg2->meta = $cfg;
                    $this->register[$name] = (array)$cfg2;
                    $this->logger->log("[OFF] '".$name."'");
                }
            } else {
                // Copy all the data into the register and enable
                $this->register[$name] = (array) $cfg;
                $this->logger->log("[ON]  '".$name."'");
            }
        } else {
            throw new ModuleException("Could not add module. '$moduleInfo_file' does not exist", 1);
        }
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public function enableModule($name, $permanent = true) {
        if (isset($this->register[$name])) {
            // Change the register
            $info = (object) $this->register[$name];

            // Do nothing if it is already enabled
            if (isset($info->enabled)) {
                if ($info->enabled) {
                    $this->logger->logWarning("Could not enable module '".$name."'. Module is already enabled.");
                    return false;
                }
            }

            // Otherwise move data from meta to the module config
            $info = $info->meta;
            $info->enabled = true;
            $this->register[$name] = (array)$info;

            $this->logger->log("Enabled module '".$name."'");

            // Enable it permanently if so desired
            if ($permanent) {
                $file = $info->directory . "/moduleInfo.php";
                $this->setModuleValue($file, 'enabled', true);
            }

            // Reload the eventRegister
            $this->events->buildEventRegister();
        } else {
            throw new ModuleException("Could not enable module '".$name."'. Module does not exist.", 1);
        }   
    }

    /**
     * @throws FuzeWorks\ModuleException
     */
    public function disableModule($name, $permanent = true) {
        if (isset($this->register[$name])) {
            $info = (object) $this->register[$name];

            // Do nothing if it is already disabled
            if (isset($info->meta)) {
                $this->logger->logWarning("Could not disable module '".$name."'. Module is already disabled.");
                return false;
            }

            $disabled = new StdClass();
            $disabled->meta = $info;
            $disabled->directory = $info->directory;
            $disabled->module_name = $info->module_name;

            $this->register[$name] = (array)$disabled;
            $this->logger->log("Disabled module '".$name."'");
            if ($permanent) {
                $file = $info->directory . "/moduleInfo.php";
                $this->setModuleValue($file, 'enabled', false);
            }

            // Reload the eventRegister
            $this->events->buildEventRegister();

            // Remove the existence of the module
            unset($this->core->mods->{strtolower($info->module_name)});
        } else {
            throw new ModuleException("Could not disable module '".$name."'. Module does not exist.", 1);
        }

    }

    public function buildRegister() {
        $this->logger->newLevel("Loading Module Headers", 'Core');

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
                        $this->logger->log("[ON]  '".$name."'");
                    } else {
                        // If not, copy all the basic data so that it can be enabled in the future
                        $cfg2 = new StdClass();
                        $cfg2->module_name = $cfg->module_name;
                        $cfg2->directory = $cfg->directory;
                        $cfg2->meta = $cfg;
                        $register[$name] = (array)$cfg2;
                        $this->logger->log("[OFF] '".$name."'");
                    }
                } else {
                    // Copy all the data into the register and enable
                    $register[$name] = (array) $cfg;
                    $this->logger->log("[ON]  '".$name."'");
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
                $this->logger->log("[ON]  '".$name."'");
            }
        }

        $this->register = $register;
        $this->logger->stopLevel();
        
    }

}
 
?>