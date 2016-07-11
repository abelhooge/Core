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
 * Simple Template Engine that allows for PHP templates.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class PHPEngine implements TemplateEngine
{
    /**
     * The currently used directory by the template.
     *
     * @var string
     */
    protected $directory;

    /**
     * All the currently assigned variables.
     *
     * @var array
     */
    protected $assigned_variables = array();

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function get($file, $assigned_variables)
    {
        // First set all the variables
        $this->assigned_variables = $assigned_variables;
        $vars = $this->assigned_variables;
        $directory = $this->directory;

        // Then run the file
        if (!is_null($file)) {
            ob_start();
            include $file;

            return ob_get_clean();
        }
    }

    public function getFileExtensions()
    {
        return array('php');
    }

    public function reset()
    {
        $this->directory = null;
        $this->assigned_variables = array();
    }
}