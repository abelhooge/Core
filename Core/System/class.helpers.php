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
 * @todo Add documentation
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Helpers
{

    protected static $helpers = array();

    protected static $helperPaths = array('Application'.DS.'Helpers', 'Core'.DS.'Helpers');

    public static function load($helperName, $directory = null)
    {
        // First determine the name of the helper
        $helperName = strtolower(str_replace(array('_helper', '.php'), '', $helperName).'_helper');
        
        // Determine what directories should be checked
        $directories = (is_null($directory) ? self::$helperPaths : array($directory));

        // Check it is already loaded
        if (isset($helpers[$helperName]))
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
            $baseHelper = 'Core'.DS.'Helpers'.$helperName.'.php';
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
            self::$helpers[$event->helperName] = true;
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
                self::$helpers[$event->helperName] = true;
                Logger::log("Loading helper '".$event->helperName."'");
                return true;
            }
        }

        throw new HelperException("Could not load helper. Helper not found.", 1);
    }

    public static function get($helperName, $directory = null)
    {
        return self::load($helperName, $directory);
    }

    public static function addHelperPath($directory)
    {
        if (!in_array($directory, $directories))
        {
            $directories[] = $directory;
        }
    }

    public static function removeHelperPath($directory)
    {
        if (($key = array_search($directory, $directories)) !== false) 
        {
            unset($directories[$key]);
        }
    }

    public static function getHelperPaths()
    {
        return $directories;
    }
}