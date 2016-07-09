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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */

use FuzeWorks\Config;
use FuzeWorks\Core;
use FuzeWorks\Logger;

// Load the FuzeWorks container
$container = require('bootstrap.php');

// Load the test abstract
require_once 'abstract.coreTestAbstract.php';

// Reset error and exception handlers
ob_start();
restore_error_handler();
restore_exception_handler();

// Display all errors
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

// Set localhost "remote" IP
isset($_SERVER['REMOTE_ADDR']) OR $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Alias vfsStream
class_alias('org\bovigo\vfs\vfsStream', 'vfsStream');
class_alias('org\bovigo\vfs\vfsStreamDirectory', 'vfsStreamDirectory');
class_alias('org\bovigo\vfs\vfsStreamWrapper', 'vfsStreamWrapper');

Logger::setLoggerTemplate('logger_cli');

require_once('mocks/autoloader.php');
spl_autoload_register('autoload');