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

use FuzeWorks\TemplateEngine\JsonEngine;
use FuzeWorks\TemplateEngine\PHPEngine;
use FuzeWorks\TemplateEngine\SmartyEngine;
use FuzeWorks\TemplateEngine\LatteEngine;
use FuzeWorks\TemplateEngine\TemplateEngine;
use FuzeWorks\Exception\LayoutException;

/**
 * Layout and Template Manager for FuzeWorks.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @todo      Make Object, remove static stuff
 */
class Layout
{
    /**
     * The file to be loaded by the layout manager.
     *
     * @var null|string
     */
    public static $file = null;

    /**
     * The directory of the file to be loaded by the layout manager.
     *
     * @var string
     */
    public static $directory;

    /**
     * All assigned currently assigned to the template.
     *
     * @var array Associative Assigned Variable Array
     */
    private static $assigned_variables = array();

    /**
     * All engines that can be used for templates.
     *
     * @var array of engines
     */
    private static $engines = array();

    /**
     * All file extensions that can be used and are bound to a template engine.
     *
     * @var array of names of engines
     */
    private static $file_extensions = array();

    /**
     * whether the template engines are already called.
     *
     * @var bool True if loaded
     */
    private static $engines_loaded = false;

    /**
     * The currently selected template engine.
     *
     * @var string name of engine
     */
    private static $current_engine;

    public static function init()
    {
        self::$directory = Core::$appDir . DS .'Views';
    }

    /**
     * Retrieve a template file using a string and a directory and immediatly parse it to the output class.
     *
     * What template file gets loaded depends on the template engine that is being used.
     * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/view.dashboard.php file.
     * You can also provide no particular engine, and the manager will decide what template to load.
     * Remember that doing so will result in a LayoutException when multiple compatible files are found.
     *
     * @param string $file         File to load
     * @param string $directory    Directory to load it from
     * @param bool   $directOutput Whether to directly output the result with an echo or send it to the output class. True if echo
     *
     * @throws LayoutException On error
     */
    public static function view($file, $directory = null, $directOutput = false)
    {
        $output = Factory::getInstance()->output;
        $directory = (is_null($directory) ? self::$directory : $directory);

        if ($directOutput === true)
        {
            echo self::get($file, $directory);
        }
        else
        {
            $output->append_output(self::get($file, $directory));  
        }
        
        return;
    }

    /**
     * Retrieve a template file using a string and a directory.
     *
     * What template file gets loaded depends on the template engine that is being used.
     * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/view.dashboard.php file.
     * You can also provide no particular engine, and the manager will decide what template to load.
     * Remember that doing so will result in a LayoutException when multiple compatible files are found.
     *
     * @param string $file      File to load
     * @param string $directory Directory to load it from
     *
     * @return string The output of the template
     *
     * @throws LayoutException On error
     */
    public static function get($file, $directory = null)
    {
        $directory = (is_null($directory) ? self::$directory : $directory);
        Logger::newLevel("Loading template file '".$file."' in '".$directory."'");

        // First load the template engines
        self::loadTemplateEngines();

        // First retrieve the filepath
        if (is_null(self::$current_engine)) {
            self::setFileFromString($file, $directory, array_keys(self::$file_extensions));
        } else {
            self::setFileFromString($file, $directory, self::$current_engine->getFileExtensions());
        }

        // Then assign some basic variables for the template
        self::$assigned_variables['wwwDir'] = Config::get('main')->base_url;
        self::$assigned_variables['siteURL'] = Config::get('main')->base_url;
        self::$assigned_variables['serverName'] = Config::get('main')->server_name;
        self::$assigned_variables['adminMail'] = Config::get('main')->administrator_mail;
        self::$assigned_variables['contact'] = Config::get('contact')->toArray();

        // Select an engine if one is not already selected
        if (is_null(self::$current_engine)) {
            self::$current_engine = self::getEngineFromExtension(self::getExtensionFromFile(self::$file));
        }

        self::$current_engine->setDirectory(self::$directory);

        // And run an Event to see what other parts have to say about it
        $event = Events::fireEvent('layoutLoadViewEvent', self::$file, self::$directory, self::$current_engine, self::$assigned_variables);

        // The event has been cancelled
        if ($event->isCancelled()) {
            return false;
        }

        // And refetch the data from the event
        self::$current_engine = $event->engine;
        self::$assigned_variables = $event->assigned_variables;

        Logger::stopLevel();

        // And finally run it
        if (file_exists($event->file)) {
            return self::$current_engine->get($event->file, self::$assigned_variables);
        }

        throw new LayoutException('The requested file was not found', 1);
    }

    /**
     * Retrieve a Template Engine from a File Extension.
     *
     * @param string $extension File extention to look for
     *
     * @return object Template Engine
     */
    public static function getEngineFromExtension($extension)
    {
        if (isset(self::$file_extensions[strtolower($extension)])) {
            return self::$engines[ self::$file_extensions[strtolower($extension)]];
        }

        throw new LayoutException('Could not get Template Engine. No engine has corresponding file extension', 1);
    }

    /**
     * Retrieve the extension from a file string.
     *
     * @param string $fileString The path to the file
     *
     * @return string Extension of the file
     */
    public static function getExtensionFromFile($fileString)
    {
        return substr($fileString, strrpos($fileString, '.') + 1);
    }

    /**
     * Converts a view string to a file using the directory and the used extensions.
     *
     * It will detect whether the file exists and choose a file according to the provided extensions
     *
     * @param string $string     The string used by a controller. eg: 'dashboard/home'
     * @param string $directory  The directory to search in for the template
     * @param array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
     *
     * @return string Filepath of the template
     *
     * @throws LayoutException On error
     */
    public static function getFileFromString($string, $directory, $extensions = array())
    {
        $directory = preg_replace('#/+#', '/', (!is_null($directory) ? $directory : self::$directory).'/');

        if (strpbrk($directory, "\\/?%*:|\"<>") === TRUE || strpbrk($string, "\\/?%*:|\"<>") === TRUE)
        {
            throw new LayoutException('Could not get file. Invalid file string', 1);
        }

        if (!file_exists($directory)) {
            throw new LayoutException('Could not get file. Directory does not exist', 1);
        }

        // Set the file name and location
        $viewSelector = explode('/', $string);
        if (count($viewSelector) == 1) {
            $viewSelector = 'view.'.$viewSelector[0];
        } else {
            // Get last file
            $file = end($viewSelector);

            // Reset to start
            reset($viewSelector);

            // Remove last value
            array_pop($viewSelector);

            $viewSelector[] = 'view.'.$file;

            // And create the final value
            $viewSelector = implode('/', $viewSelector);
        }

        // Then try and select a file
        $fileSelected = false;
        $selectedFile = null;
        foreach ($extensions as $extension) {
            $file = $directory.$viewSelector.'.'.strtolower($extension);
            $file = preg_replace('#/+#', '/', $file);
            if (file_exists($file) && !$fileSelected) {
                $selectedFile = $file;
                $fileSelected = true;
                Logger::log("Found matching file: '".$file."'");
            } elseif (file_exists($file) && $fileSelected) {
                throw new LayoutException('Could not select template. Multiple valid extensions detected. Can not choose.', 1);
            }
        }

        // And choose what to output
        if (!$fileSelected) {
            throw new LayoutException('Could not select template. No matching file found.');
        }

        return $selectedFile;
    }

    /**
     * Converts a view string to a file using the directory and the used extensions.
     * It also sets the file variable of this class.
     *
     * It will detect whether the file exists and choose a file according to the provided extensions
     *
     * @param string $string     The string used by a controller. eg: 'dashboard/home'
     * @param string $directory  The directory to search in for the template
     * @param array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
     *
     * @return string Filepath of the template
     *
     * @throws LayoutException On error
     */
    public static function setFileFromString($string, $directory, $extensions = array())
    {
        self::$file = self::getFileFromString($string, $directory, $extensions);
        self::$directory = preg_replace('#/+#', '/', (!is_null($directory) ? $directory : self::$directory).'/');
    }

    /**
     * Get the current file to be loaded.
     *
     * @return null|string Path to the file
     */
    public static function getFile()
    {
        return self::$file;
    }

    /**
     * Set the file to be loaded.
     *
     * @param string $file Path to the file
     */
    public static function setFile($file)
    {
        self::$file = $file;
    }

    /**
     * Get the directory of the file to be loaded.
     *
     * @return null|string Path to the directory
     */
    public static function getDirectory()
    {
        return self::$directory;
    }

    /**
     * Set the directory of the file to be loaded.
     *
     * @param string $directory Path to the directory
     */
    public static function setDirectory($directory)
    {
        self::$directory = $directory;
    }

    /**
     * Assign a variable for the template.
     *
     * @param string $key   Key of the variable
     * @param mixed  $value Value of the variable
     */
    public static function assign($key, $value)
    {
        self::$assigned_variables[$key] = $value;
    }

    /**
     * Set the title of the template.
     *
     * @param string $title title of the template
     */
    public static function setTitle($title)
    {
        self::$assigned_variables['title'] = $title;
    }

    /**
     * Get the title of the template.
     *
     * @return string title of the template
     */
    public static function getTitle()
    {
        if (!isset(self::$assigned_variables['title']))
        {
            return false;
        }
        return self::$assigned_variables['title'];
    }

    /**
     * Set the engine for the next layout.
     *
     * @param string $name Name of the template engine
     *
     * @return bool true on success
     *
     * @throws \FuzeWorks\LayoutException on error
     */
    public static function setEngine($name)
    {
        self::loadTemplateEngines();
        if (isset(self::$engines[$name])) {
            self::$current_engine = self::$engines[$name];
            Logger::log('Set the Template Engine to '.$name);

            return true;
        }
        throw new LayoutException('Could not set engine. Engine does not exist', 1);
    }

    /**
     * Get a loaded template engine.
     *
     * @param string $name Name of the template engine
     *
     * @return object Object that implements \FuzeWorks\TemplateEngine
     */
    public static function getEngine($name)
    {
        self::loadTemplateEngines();
        if (isset(self::$engines[$name])) {
            return self::$engines[$name];
        }
        throw new LayoutException('Could not return engine. Engine does not exist', 1);
    }

    /**
     * Register a new template engine.
     *
     * @param object $engineClass          Object that implements the \FuzeWorks\TemplateEngine
     * @param string $engineName           Name of the template engine
     * @param array  $engineFileExtensions File extensions this template engine should be used for
     *
     * @return bool true on success
     *
     * @throws \FuzeWorks\LayoutException On error
     */
    public static function registerEngine($engineClass, $engineName, $engineFileExtensions = array())
    {
        // First check if the engine already exists
        if (isset(self::$engines[$engineName])) {
            throw new LayoutException("Could not register engine. Engine '".$engineName."' already registered", 1);
        }

        // Then check if the object is correct
        if ($engineClass instanceof TemplateEngine) {
            // Install it
            self::$engines[$engineName] = $engineClass;

            // Then define for what file extensions this Template Engine will work
            if (!is_array($engineFileExtensions)) {
                throw new LayoutException('Could not register engine. File extensions must be an array', 1);
            }

            // Then install them
            foreach ($engineFileExtensions as $extension) {
                if (isset(self::$file_extensions[strtolower($extension)])) {
                    throw new LayoutException('Could not register engine. File extension already bound to engine', 1);
                }

                // And add it
                self::$file_extensions[strtolower($extension)] = $engineName;
            }

            // And log it
            Logger::log('Registered Template Engine: '.$engineName);

            return true;
        }

        throw new LayoutException("Could not register engine. Engine must implement \FuzeWorks\TemplateEngine", 1);
    }

    /**
     * Load the template engines by sending a layoutLoadEngineEvent.
     */
    public static function loadTemplateEngines()
    {
        if (!self::$engines_loaded) {
            Events::fireEvent('layoutLoadEngineEvent');

            // Load the engines provided in this file
            self::registerEngine(new PHPEngine(), 'PHP', array('php'));
            self::registerEngine(new JsonEngine(), 'JSON', array('json'));
            self::registerEngine(new SmartyEngine(), 'Smarty', array('tpl'));
            self::registerEngine(new LatteEngine(), 'Latte', array('latte'));
            self::$engines_loaded = true;
        }
    }

    /**
     * Calls a function in the current Template engine.
     *
     * @param string     $name   Name of the function to be called
     * @param Paramaters $params Parameters to be used
     *
     * @return mixed Function output
     */
    public static function __callStatic($name, $params)
    {
        // First load the template engines
        self::loadTemplateEngines();

        if (!is_null(self::$current_engine)) {
            // Call user func array here
            return call_user_func_array(array(self::$current_engine, $name), $params);
        }
        throw new LayoutException('Could not access Engine. Engine not loaded', 1);
    }

    /**
     * Resets the layout manager to its default state.
     */
    public static function reset()
    {
        if (!is_null(self::$current_engine)) {
            self::$current_engine->reset();
        }

        // Unload the engines
        self::$engines = array();
        self::$engines_loaded = false;
        self::$file_extensions = array();

        self::$current_engine = null;
        self::$assigned_variables = array();
        self::$directory = Core::$appDir . DS . 'Views';
        Logger::log('Reset the layout manager to its default state');
    }
}