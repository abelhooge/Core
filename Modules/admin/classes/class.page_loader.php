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

use FuzeWorks\Logger;
use FuzeWorks\Layout;
use FuzeWorks\Modules;

class PageLoader
{
    private static $pageList;
    private static $activePage;
    private static $html;
    private static $breadcrumbs = array();
    private static $title;

    /**
     * Set the pageList.
     *
     * @param PageList $pageList PageList
     */
    public static function setPageList($pageList)
    {
        self::$pageList = $pageList;
    }

    public static function loadPage($matches)
    {
        // First check if any data is given at all
        Logger::newLevel('Retrieving page from module');
        if (!isset($matches['identifier']) && !isset($matches['page'])) {
            // If nothing is provided, load the dashboard
            Logger::log('No input retrieved. Loading dashboard');

            return self::dashboard();
        } elseif (!isset($matches['identifier']) || !isset($matches['page'])) {
            // If incomplete data is provided, load a 404
            Logger::log('Invalid input retrieved. Loading 404 not found page');

            return self::error404();
        }

        // If enough data is provided, try and load a page
        Logger::log('Input received. Attempting to find page');
        $unique_identifier = $matches['identifier'].'/'.$matches['page'];
        if (isset(self::$pageList->pages[$unique_identifier])) {
            // Page found, start loading process
            $page = self::$pageList->getPage($unique_identifier);

            // Load the designated module
            $module = Modules::get($page->getModule());

            // Check if it implements the PageRouterInterface
            if (method_exists($module, 'getAdminRouter')) {
                $router = $module->getAdminRouter();

                // Then check if the router is valid, if not, return 500
                if (!$router instanceof PageRouterInterface) {
                    Logger::logError("Could not load '".$unique_identifier."' on module '".$page->getModule()."'. ".get_class($router)." does not implement \Module\Admin\PageRouterInterface");

                    return self::error500();
                }

                // Route the request into the module
                Logger::log('Input valid and module loaded. Attempting to route request');

                // Generating page object
                $pageObject = new Page();
                $pageObject->setPagePath($matches['page']);
                $pageObject->setSubPath((isset($matches['subdata']) ? $matches['subdata'] : null));

                // And send it
                $router->importPage($pageObject);
                $router->route();

                // And retrieve it
                $pageObject = $router->getPage();

                // And retrieve some data
                self::$html = $pageObject->getHtml();
                // $html = $pageObject->getHtml();

                Logger::stopLevel();

                return '';
            } else {
                // Error, router does not exist
                Logger::logError("Could not load '".$unique_identifier."' on module '".$page->getModule()."'. ".get_class($module).' does not implement method getAdminRouter()');
                Logger::stopLevel();

                return self::error500();
            }
        }

        Logger::log('Matching page was not found. Loading 404 not found page');
        Logger::stopLevel();

        return self::error404();
    }

    public static function getHtml()
    {
        return self::$html;
    }

    public static function getActivePage()
    {
        return 'fuzeadmin/testPage';
    }

    public static function dashboard()
    {
    }

    public static function error404()
    {
        Logger::http_error(404, false);

        return Layout::get('404');
    }

    public static function error500()
    {
        Logger::http_error(500, false);

        return Layout::get('500');
    }
}
