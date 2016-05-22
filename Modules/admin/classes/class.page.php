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

class Page
{
    private $pagePath;
    private $subPath = array();
    private $html;
    private $title;
    private $breadcrumbs = array();

    /**
     * Gets the value of pagePath.
     *
     * @return mixed
     */
    public function getPagePath()
    {
        return $this->pagePath;
    }

    /**
     * Sets the value of pagePath.
     *
     * @param mixed $pagePath the page path
     *
     * @return self
     */
    public function setPagePath($pagePath)
    {
        $this->pagePath = $pagePath;

        return $this;
    }

    /**
     * Gets the value of subPath.
     *
     * @return mixed
     */
    public function getSubPath()
    {
        return $this->subPath;
    }

    /**
     * Sets the value of subPath.
     *
     * @param mixed $subPath the sub path
     *
     * @return self
     */
    public function setSubPath($subPath)
    {
        $this->subPath = $subPath;

        return $this;
    }

    /**
     * Gets the value of html.
     *
     * @return mixed
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Sets the value of html.
     *
     * @param mixed $html the html
     *
     * @return self
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Gets the value of title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the value of title.
     *
     * @param mixed $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the value of breadcrumbs.
     *
     * @return mixed
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Sets the value of breadcrumbs.
     *
     * @param mixed $breadcrumbs the breadcrumbs
     *
     * @return self
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }
}
