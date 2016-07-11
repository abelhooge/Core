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

use FuzeWorks\Core;
use Smarty;

/**
 * Wrapper for the Smarty Template Engine.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class SmartyEngine implements TemplateEngine
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

    /**
     * Instance of the Smarty Template Engine.
     *
     * @var \Smarty
     */
    protected $smartyInstance;

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function get($file, $assigned_variables)
    {
        // First set all the variables
        $this->assigned_variables = $assigned_variables;

        // Load Smarty
        $this->loadSmarty();

        // Set the directory
        $this->smartyInstance->setTemplateDir($this->directory);

        // Then assign all variables
        foreach ($this->assigned_variables as $key => $value) {
            $this->smartyInstance->assign($key, $value);
        }

        // And finally, load the template
        return $this->smartyInstance->fetch($file);
    }

    /**
     * Loads a Smarty instance if it is not already loaded.
     */
    private function loadSmarty()
    {
        if (is_null($this->smartyInstance)) {
            $this->smartyInstance = new Smarty();

            // Then prepare all variables
            $this->smartyInstance->setCompileDir(Core::$tempDir . DS . 'Smarty' . DS . 'Compile');
            $this->smartyInstance->setCacheDir(Core::$tempDir . DS . 'Smarty');
        }
    }

    public function getFileExtensions()
    {
        return array('tpl');
    }

    public function reset()
    {
        $this->smartyInstance = null;
        $this->directory = null;
        $this->assigned_variables = array();
    }

    /**
     * Retrieve a value from Smarty.
     *
     * @param string $name Variable name
     *
     * @return mixed Variable Value
     *
     * @throws \FuzeWorks\LayoutException on error
     */
    public function __get($name)
    {
        // First load Smarty
        $this->loadSmarty();

        return $this->smartyInstance->$name;
    }

    /**
     * Set a variable in Smarty.
     *
     * @param string $name  Variable Name
     * @param mixed  $value Variable Value
     *
     * @throws \FuzeWorks\LayoutException on error
     */
    public function __set($name, $value)
    {
        // First load Smarty
        $this->loadSmarty();

        $this->smartyInstance->$name = $value;
    }

    /**
     * Calls a function in Smarty.
     *
     * @param string     $name   Name of the function to be called
     * @param Paramaters $params Parameters to be used
     *
     * @return mixed Function output
     */
    public function __call($name, $params)
    {
        // First load Smarty
        $this->loadSmarty();

        return call_user_func_array(array($this->smartyInstance, $name), $params);
    }
}