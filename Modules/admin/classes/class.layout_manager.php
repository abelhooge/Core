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

namespace Module\Admin;

use FuzeWorks\Layout;
use FuzeWorks\Config;

class LayoutManager
{
    private static $pageList;
    private static $matches;

    /**
     * Load the panel of this admin interface.
     *
     * @throws AdminException
     *
     * @return string HTML
     */
    public static function loadPanel()
    {
        // First check if the pageList is set and valid
        if (is_null(self::$pageList)) {
            throw new AdminException('Can not load panel. PageList is not set', 1);
        }

        // Load the theme
        ThemeManager::loadTheme();

        // Set the PageLoader variables
        PageLoader::setPageList(self::$pageList);

        // Get the page we are trying to load
        PageLoader::loadPage(self::$matches);
        $html = PageLoader::getHtml();
        $activePage = PageLoader::getActivePage();

        // And add the pageList
        Layout::assign('pageList', self::$pageList);
        Layout::assign('activePage', $activePage);
        Layout::assign('pageHTML', $html);

        // And add more basic variables
        foreach (self::getVariables() as $key => $value) {
            Layout::assign($key, $value);
        }

        // And load the file
        return Layout::get('panel');
    }

    public static function loadPanelAPI()
    {
    }

    public static function loadLogin()
    {
    }

    public static function loadLoginAPI()
    {
    }

    /**
     * Set the pageList.
     *
     * @param PageList $pageList PageList
     */
    public static function setPageList($pageList)
    {
        self::$pageList = $pageList;
    }

    public static function setMatches($matches)
    {
        self::$matches = $matches;
    }

    /**
     * Get all the basic variables required for every template.
     *
     * @return array with settings
     */
    private static function getVariables()
    {
        $vars = array();
        $vars['adminURL'] = Config::get('main')->SITE_URL.'admin/';

        return $vars;
    }
}
