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
use \FuzeWorks\Logger;

class AdvertiseFetcher {

	/**
	 * Create a pageList using the advertisements from the admin module
	 * @param  array  $advertisements The advertisement repository
	 * @throws AdminException         On fatal and/or missing data
	 * @return PageList               An object oriented page list with details
	 * @todo   Implement Unit Test
	 */
	public static function getPageList($advertisements) {
		// Prepare a pageList
		Logger::newLevel('Generating PageList');
		$pageList = new PageList();

		// First get the modules
		foreach ($advertisements as $module => $data) {
			// Apple regular data
			$identifier = 	(isset($data['identifier']) ? $data['identifier'] : null);
			$pages = 		(isset($data['pages']) ? $data['pages'] : null);

			// Check if everything is set, if not shout out in terror
			if (is_null($identifier) || is_null($pages))
				throw new AdminException("Incomplete data for module '".$module."'", 1);

			// And then the pages
			foreach ($pages as $pageData) {
				// Create page variable and add module and identifier
				$page = new PageData();
				$page->setModule($module);
				$page->setIdentifier($identifier);

				// First the required data for every page
				$page_path = 	(isset($pageData['page_path']) ? $pageData['page_path'] : null);
				$name = 		(isset($pageData['name']) ? $pageData['name'] : null);

				// And throw errors if non-existent
				if (is_null($page_path) || is_null($name))
					throw new AdminException("Incomplete data for module '".$module."'", 1);

				// And set those values
				$page->setPagePath($page_path);
				$page->setName($name);
				$unique_identifier = $identifier . "/" . $page_path;
				Logger::log("Adding page '".$name."' on '".$unique_identifier."'");



				// And at last for the optional values
				$page->setIcon( (isset($pageData['icon']) ? $pageData['icon'] : '') );
				$page->setPermissionGroups( (isset($pageData['permissionGroups']) ? $pageData['permissionGroups'] : array()) );
				$page->setPriority( (isset($pageData['priority']) ? $pageData['priority'] : 1) );

				// And finally add the page
				$pageList->addPage($page, $unique_identifier);
			}
		}

		// And finally return the pageList\
		Logger::stopLevel();
		return $pageList;
	}
}

?>