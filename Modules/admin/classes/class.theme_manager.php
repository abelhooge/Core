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

namespace Module\Admin;
use \FuzeWorks\Layout;
use \FuzeWorks\Logger;

class ThemeManager {

	private static $themeDir = 'views/adminlte2.1/';

	public static function loadTheme($theme = null) {
		self::$themeDir = Main::getModulePath() . 'themes/adminlte2.1/';
		Layout::setDirectory(self::$themeDir);
	}

	public static function getDirectory() {
		// First check if the theme is actually loaded
		if (empty(self::$themeDir))
			throw new AdminException("Could not load panel. Theme not loaded", 1);
		// And then return the theme Directory
		return self::$themeDir;
	}

}

?>