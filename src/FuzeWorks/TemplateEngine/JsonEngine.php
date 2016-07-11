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
 
namespace FuzeWorks\TemplateEngine;

/**
 * Template Engine that exports all assigned variables as JSON.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class JsonEngine implements TemplateEngine
{
    /**
     * All the currently assigned variables.
     *
     * @var array
     */
    protected $assigned_variables = array();

    /**
     * Whether the JSON data should be parsed or left as is.
     *
     * @var bool true if to be parsed
     */
    protected static $string_return = true;

    /**
     * Whether the JSON data should be parsed or left as is.
     *
     * @param true if to be parsed
     */
    public static function returnAsString($boolean = true)
    {
        self::$string_return = $boolean;
    }

    public function setDirectory($directory)
    {
        return true;
    }

    public function get($file, $assigned_variables)
    {
        // First set all the variables
        $this->assigned_variables = $assigned_variables;

        // First set up the JSON array
        $json = array();

        // Look up if a file is provided
        if (!is_null($file)) {
            // Retrieve a file
            $string = file_get_contents($file);
            $json = json_decode($string, true);
        }

        // Then assign all variables
        $json['data'] = $this->assigned_variables;

        // And return it
        if (self::$string_return) {
            return json_encode($json);
        }

        return $json;
    }

    public function getFileExtensions()
    {
        return array('json');
    }

    public function reset()
    {
        $this->assigned_variables = array();
        self::$string_return = true;
    }

    public function test($param1, $param2, $param3)
    {
        return array($param1, $param2, $param3);
    }
}