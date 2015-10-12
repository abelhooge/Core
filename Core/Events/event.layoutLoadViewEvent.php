<?php
/**
 * FuzeWorks
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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

namespace FuzeWorks\Event;
use \FuzeWorks\Event;

/**
 * Event that gets loaded when a view is loaded.
 *
 * Use this to cancel the loading of a view, or change the file or engine of a view
 *
 * @package     net.techfuze.fuzeworks.core.event
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class LayoutLoadViewEvent extends Event {

	/**
	 * The directory of the view to be loaded
	 * @var string
	 */
    public $directory;

    /**
     * The file of the view to be loaded
     * @var string
     */
    public $file;

    /**
     * The engine the file will be loaded with
     * @var object
     */
    public $engine;

    /**
     * The assigned variables to the template
     * @var array
     */
    public $assigned_variables;

    public function init($file, $directory, $engine, $assigned_variables){
        $this->file = $file;
        $this->directory = $directory;
        $this->engine = $engine;
        $this->assigned_variables = $assigned_variables;
    }
}

?>