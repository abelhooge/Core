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

namespace FuzeWorks\Event;

use FuzeWorks\Event;

/**
 * Event that gets fired when a controller is loaded.
 *
 * Use this to cancel the loading of a controller, or change the details of what is loaded.
 *
 * Currently only used by Router::defaultCallable();
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class RouterLoadControllerEvent extends Event
{
    /**
     * The directory where the controller is located
     *
     * @var string
     */
    public $directory;

    /**
     * The file in which the controller is found
     *
     * @var string
     */
    public $file;

    /**
     * The class of the controller
     *
     * @var string
     */
    public $className;

    /**
     * The name of the controller
     *
     * @var string
     */
    public $controller;

    /**
     * The function that will be loaded in the controller
     *
     * @var string
     */
    public $function;

    /**
     * The parameters that will be provided to the function in the controller
     *
     * @var string|null
     */
    public $parameters;

    public function init($file, $directory, $className, $controller, $function, $parameters)
    {
        $this->file = $file;
        $this->directory = $directory;
        $this->className = $className;
        $this->controller = $controller;
        $this->function = $function;
        $this->parameters = $parameters;
    }
}
