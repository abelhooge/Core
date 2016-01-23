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
use \FuzeWorks\Module;

/**
 * Admin Module
 *
 * Admin panel module controller
 * @package     net.techfuze.fuzeworks.admin
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Main {
	use Module;

	/**
	 * Loads the module and registers the events
	 *
	 * @access public
	 */
	public function onLoad() {
		require_once(self::getModulePath() . "/classes/class.layout_manager.php");
		require_once(self::getModulePath() . "/classes/class.admin_exception.php");
		require_once(self::getModulePath() . "/classes/class.theme_manager.php");
		require_once(self::getModulePath() . "/classes/class.advertise_fetcher.php");
		require_once(self::getModulePath() . "/classes/class.page_loader.php");
		require_once(self::getModulePath() . "/classes/class.page_data.php");
		require_once(self::getModulePath() . "/classes/class.page.php");
		require_once(self::getModulePath() . "/classes/class.page_list.php");
		require_once(self::getModulePath() . "/classes/class.page_router_interface.php");
		require_once(self::getModulePath() . "/classes/class.page_interface.php");
		require_once(self::getModulePath() . "/classes/class.admin_router.php");
	}

	/**
	 * Gets called when the path matches the regex of this module.
	 * @access public
	 * @param  array   Regex matches
	 * @return void
	 */
	public function route($matches = array()) {
		// First create a pageList based on the advertisements
		$advertisements = self::getAdvertisements('admin');
		if (!is_array($advertisements))
			throw new AdminException("Could not load advertised modules. Malformed object retrieved", 1);

		$pageList = AdvertiseFetcher::getPageList($advertisements);

		// After that, load the authenticator and check if user is logged in
		// @TODO IMPLEMENT

		// After that, load the theme that is set
		LayoutManager::setPageList($pageList);
		LayoutManager::setMatches($matches);
		$html = LayoutManager::loadPanel();

		// And print it
		echo $html;
	}

	public function getAdminRouter() {
		return new AdminRouter();
	}

}

?>