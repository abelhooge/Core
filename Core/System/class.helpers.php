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
 * Helpers Class.
 *
 * Helpers, as the name suggests, help you with tasks. 
 * 
 * Each helper file is simply a collection of functions in a particular category.
 * There are URL Helpers, that assist in creating links, there are Form Helpers that help you create form elements, 
 * Text Helpers perform various text formatting routines, Cookie Helpers set and read cookies, 
 * File Helpers help you deal with files, etc.
 *
 * Unlike most other systems in FuzeWorks, Helpers are not written in an Object Oriented format. 
 * They are simple, procedural functions. Each helper function performs one specific task, with no dependence on other functions.
 *
 * FuzeWorks does not load Helper Files by default, so the first step in using a Helper is to load it. Once loaded, 
 * it becomes globally available to everything. 
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Helpers
{

    /**
     * Array of loadedHelpers, so that they won't be reloaded
     * 
     * @var array Array of loaded helperNames
     */
    protected $helpers = array();

    /**
     * Paths where Helpers can be found. 
     * 
     * Libraries will only be loaded if either a directory is supplied or it is in one of the helperPaths
     * 
     * @var array Array of paths where helpers can be found
     */
    protected $helperPaths = array();

    public function __construct()
    {
        $this->helperPath = [
            Core::$appDir . DS . 'Helpers',
            Core::$coreDir . DS . 'Helpers'
        ];
    }

    /**
     * Load a helper.
     * 
     * Supply the name and the helper will be loaded from the supplied directory,
     * or from one of the helperPaths (which you can add).
     * 
     * @param string        $helperName Name of the helper
     * @param string|null   $directory  Directory to load the helper from, will ignore $helperPaths
     * @return bool                     Whether the helper was succesfully loaded (true if yes)
     */
    public function load($helperName, $directory = null)
    {
        // First determine the name of the helper
        $helperName = strtolower(str_replace(array('_helper', '.php'), '', $helperName).'_helper');
        
        // Determine what directories should be checked
        $directories = (is_null($directory) ? $this->helperPaths : array($directory));

        // Check it is already loaded
        if (isset($this->helpers[$helperName]))
        {
            Logger::log("Helper '".$helperName."' is already loaded. Skipping");
            return false;
        }

        // First check if there is an 'extension' class
        $extendedHelper = Config::get('main')->application_prefix . $helperName;
        $extendedHelperLoaded = false;
        foreach ($directories as $helperPath) 
        {
            $file = $helperPath . DS . $extendedHelper . '.php';
            if (file_exists($file))
            {
                $extendedHelperLoaded = true;
                $extendedHelperFile = $file;
            }
        }

        // If an extension is loaded there needs to be a base helper
        if ($extendedHelperLoaded)
        {
            $baseHelper = Core::$coreDir . DS . 'Helpers' . DS . $helperName.'.php';
            if (!file_exists($baseHelper))
            {
                throw new HelperException("Could not load helper. Base Helper not found while Extension loaded", 1);
            }

            // Fire the associated event
            $event = Events::fireEvent('helperLoadEvent', $helperName, $baseHelper, $extendedHelper, $extendedHelperFile);
            if ($event->isCancelled()) 
            {
                Logger::log("Not loading helper. Aborted by event");
                return false;
            }

            include_once($event->extendedHelperFile);
            include_once($event->helperFile);
            $this->helpers[$event->helperName] = true;
            Logger::log("Loading base helper '".$event->helperName."' and extended helper '".$event->extendedHelperName."'");
            return true;
        }

        // If no extension exists, try loading a regular helper
        foreach ($directories as $helperPath) 
        {
            $file = $helperPath . DS . $helperName . '.php';
            if (file_exists($file))
            {

                // Fire the associated event
                $event = Events::fireEvent('helperLoadEvent', $helperName, $file);
                if ($event->isCancelled()) 
                {
                    Logger::log("Not loading helper. Aborted by event");
                    return false;
                }

                include_once($event->helperFile);
                $this->helpers[$event->helperName] = true;
                Logger::log("Loading helper '".$event->helperName."'");
                return true;
            }
        }

        throw new HelperException("Could not load helper. Helper not found.", 1);
    }

    /**
     * Alias for load
     * @see load
     * 
     * @param string        $helperName Name of the helper
     * @param string|null   $directory  Directory to load the helper from, will ignore $helperPaths
     * @return bool                     Whether the helper was succesfully loaded (true if yes)
     */
    public function get($helperName, $directory = null)
    {
        return $this->load($helperName, $directory);
    }

    /**
     * Add a path where helpers can be found
     * 
     * @param string $directory The directory
     * @return void
     */
    public function addHelperPath($directory)
    {
        if (!in_array($directory, $this->helperPaths))
        {
            $this->helperPaths[] = $directory;
        }
    }

    /**
     * Remove a path where helpers can be found
     * 
     * @param string $directory The directory
     * @return void
     */    
    public function removeHelperPath($directory)
    {
        if (($key = array_search($directory, $this->helperPaths)) !== false) 
        {
            unset($this->helperPaths[$key]);
        }
    }

    /**
     * Get a list of all current helperPaths
     * 
     * @return array Array of paths where helpers can be found
     */
    public function getHelperPaths()
    {
        return $this->helperPaths;
    }
}