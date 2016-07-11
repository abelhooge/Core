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

use FuzeWorks\Exception\LayoutException;
use FuzeWorks\Core;
use Latte\Engine as Latte;

/**
 * Wrapper for the Latte Engine from Nette Framework.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class LatteEngine implements TemplateEngine
{

    /**
     * Instance of the Latte Engine
     * 
     * @var Latte\Engine The Latte Engine to be used
     */
    protected $latte;

    /**
     * Set the directory of the current template.
     *
     * @param string $directory Template Directory
     */
    public function setDirectory($directory)
    {
        if (class_exists('\Latte\Engine', true))
        {
            // If possible, load Latte\Engine
            $this->latte = new Latte;
            $this->latte->setTempDirectory(realpath(Core::$tempDir . DS . 'Latte'));
        }
        else
        {
            throw new LayoutException("Could not load LatteEngine. Is it installed or Composer not loaded?", 1);
        }
    }

    /**
     * Handle and retrieve a template file.
     *
     * @param string $file               Template File
     * @param array  $assigned_variables All the variables used in this view
     *
     * @return string Output of the template
     */
    public function get($file, $assigned_variables)
    {
        $this->latte->render($file, $assigned_variables);
    }

    /**
     * Retrieve the file extensions that this template engine uses.
     *
     * @return array All used extensions. eg: array('php')
     */
    public function getFileExtensions()
    {
        return array('latte');
    }

    /**
     * Reset the template engine to its default state, so it can be used again clean.
     */
    public function reset()
    {
        // If possible, load Latte\Engine
        $this->latte = null;
    }
}