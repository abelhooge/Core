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

return array(

    // The class name of the module. This class will be loaded upon requesting the module
    'module_class'    => 'Module\Example\Main',

    // The file that will be loaded upon requesting the module
    'module_file'     => 'class.main.php',

    // The name of the module; Appended into Modules::get('namespace/module_name');
    'module_name'     => 'Example',

    // Wether this module is an abstract. Making this abstract will only load the file, but not the class.
    'abstract'      => false,

    // Other names for this module. Setting an alias will allow you to load the module with a different name.
    'aliases'       => array(),

    // Array of modules that should be loaded before this module
    'dependencies'  => array(),

    // Events that this module listens for. When the exampleEvent is fired, this module will be loaded so the module can handle the event
    'events'        => array('exampleEvent'),

    // Routes that this module listens on. If the URL /example/ gets called, this module will be loaded
    // Everything after /example/ will be sent to the route() function in the matches array under the 'data' key
    // A route must ALWAYS have the module capturing group. Otherwise it will fail
    'routes'        => array('/^example(|\/(?P<data>.*?))$/'),

    // The name of the module as it will be logged. This does not affect usage of the module in any way
    'name'          => 'FuzeWorks Example Module',

    // A description of the module.
    'description'   => 'A descriptive module that functions as an example',

    // The author of the module. The author is the first part of the module name used for requesting. eg mycorp/example
    'author'        => 'MyCorp',

    // The current version of the module. Will be used for looking for updates
    'version'       => '1.0.0',

    // The website to look at for the module update
    'website'       => 'http://fuzeworks.techfuze.net/',

    // The initial creation of the module.
    'date_created'  => '29-04-2015',

    // The last update of this module
    'date_updated'  => '29-04-2015',

    // Wether the module is enabled or not. If it is disabled, it can not be loaded.
    'enabled'       => true
);
