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

    'module_class'    => 'Module\Admin\Main',
    'module_file'     => 'class.main.php',
    'module_name'     => 'Admin',
    'abstract'      => false,
    'aliases'       => array(),
    'dependencies'  => array(),
    'events'        => array(),
    'routes'        => array('/^admin(|\/(?P<identifier>.*?)(|\/(?<page>.*?)(|\/(?P<subdata>.*?))))$/'),
    'advertise'     => array('admin' => array( 'identifier' => 'fuzeadmin', 'pages' => array( array( 'name' => 'TEST', 'page_path' => 'testPage', 'icon' => 'fa-plane'))    )),
    'listenFor'     => array('admin'),
    'name'          => 'FuzeWorks Admin Panel',
    'description'   => 'Control Panel for FuzeWorks Modules and FrameWork',
    'author'        => 'core',
    'version'       => '1.0.0',
    'website'       => 'http://fuzeworks.techfuze.net/',
    'date_created'  => '13-01-2016',
    'date_updated'  => '17-01-2016',
    'enabled'       => true
);
