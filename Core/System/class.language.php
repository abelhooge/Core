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
 * Language Class.
 * 
 * The Language Class provides functions to retrieve language files and lines of text 
 * for purposes of internationalization.
 * 
 * In your FuzeWorks Core folder, you will find a Language sub-directory containing a set of language files for the english idiom. 
 * The files in this directory (Core/Language/english/) define the regular messages, error messages, and other generally output terms 
 * or expressions, for the different parts of the FuzeWorks.
 * 
 * You can create or incorporate your own language files, as needed, in order to provide application-specific error and other messages, 
 * or to provide translations of the core messages into other languages. These translations or additional messages would go inside your Application/Language/ directory, 
 * with separate sub-directories for each idiom (for instance, ‘french’ or ‘german’).
 * 
 * FuzeWorks comes with a set of language files for the “english” idiom. Additional approved translations for different idioms may be found in the FuzeWorks Archives. 
 * Each archive deals with a single idiom.
 * 
 * When  FuzeWorks loads language files, it will load the one in Core/Language/ first and will then look for an override in your Application/Language/ directory.
 * 
 *
 * @author  TechFuze <contact@techfuze.net>
 * @copyright (c) 2013 - 2014, TechFuze. (https://techfuze.net)
 */
class Language
{
    
    /**
     * Paths where the class can find translations
     * @var array
     */
    protected static $languagePaths = array();
    
    /**
     * The current language array
     * @var array
     */
    protected static $language = array();
    
    /**
     * Array list of all the loaded languages
     * @var array
     */
    protected static $is_loaded = array();
    
    public static function init()
    {
        self::$languagePaths[] = Core::$appDir . DS . 'Language';
    }

    /**
     * Retrieve a language file and return the language array
     * 
     * @param string $langfile
     * @param string $idiom
     * @param boolean $add_suffix
     * @param string $alt_path
     * @return type
     * @throws LanguageException
     */
    public static function get($langfile, $idiom = '', $add_suffix = TRUE, $alt_path = '')
    {
        // First we determine the file that should be loaded
        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix === TRUE)
        {
            $langfile = preg_replace('/_lang$/', '', $langfile).'_lang';
        }

        $langfile .= '.php';

        // Then we determine the idiom
        if (empty($idiom) OR ! preg_match('/^[a-z_-]+$/i', $idiom))
        {
            $config = Config::get('main');
            $idiom = empty($config->language) ? 'english' : $config->language;
        }

        // Is it already loaded? Return the entire language array
        if (isset(self::$is_loaded[$langfile]) && self::$is_loaded[$langfile] === $idiom)
        {
            return self::$language;
        }

        // Prepare the language variable
        $lang = array();

        // Load the base file, so any others found can override it
        $basepath = Core::$coreDir . DS. 'Language'.DS.$idiom.DS.$langfile;
        if (($found = file_exists($basepath)) === TRUE)
        {
            $lang = array_merge($lang, (array) include($basepath));
        }

        // Do we have an alternative path to look in?
        if ($alt_path !== '')
        {
            $alt_path .= 'Language'.DS.$idiom.DS.$langfile;
            if (file_exists($alt_path))
            {
                $lang = array_merge($lang, (array) include($alt_path));
                $found = TRUE;
            }
        }
        else
        {
            foreach (self::$languagePaths as $languagePath)
            {
                $languagePath .= DS.$idiom.DS.$langfile;
                if ($basepath !== $languagePath && file_exists($languagePath))
                {
                    $lang = array_merge($lang, (array) include($languagePath));
                    $found = TRUE;
                    break;
                }
            }
        }

        // If nothing is found, kill it
        if ($found !== TRUE)
        {
            throw new LanguageException('Unable to load the requested language file: language/'.$idiom.'/'.$langfile, 1);
        }

        // If only empty content is found, return the language array
        if ( empty($lang) )
        {
            Logger::logError('Language file contains no data: language/'.$idiom.'/'.$langfile);

            return self::$language;
        }

        // Save the data and return it
        self::$is_loaded[$langfile] = $idiom;
        self::$language = array_merge(self::$language, $lang);

        Logger::log('Language file loaded: language/'.$idiom.'/'.$langfile);
        return self::$language;
    }
    
    /**
     * Load a single line from the language array
     * 
     * @param string $line
     * @param boolean $log_errors
     * @return string
     */
    public static function line($line, $log_errors = TRUE)
    {
        $value = isset(self::$language[$line]) ? self::$language[$line] : FALSE;

        // Because killer robots like unicorns!
        if ($value === FALSE && $log_errors === TRUE)
        {
            Logger::logError('Could not find the language line "'.$line.'"');
        }

        return $value;
    }
    
    /**
     * Add a language path to the class
     * 
     * @param string $directory
     */
    public static function addLanguagePath($directory)
    {
        if (!in_array($directory, self::$languagePaths))
        {
            self::$languagePaths[] = $directory;
        }
    }
    
    /**
     * Remove a languagePath from the class
     * 
     * @param string $directory
     */
    public static function removeLanguagePath($directory)
    {
        if (($key = array_search($directory, self::$languagePaths)) !== false) 
        {
            unset(self::$languagePaths[$key]);
        }
    }
    
    /**
     * Retrieve an array of the languagePaths
     * 
     * @return array
     */
    public static function getLanguagePaths()
    {
        return self::$languagePaths;
    }
}
