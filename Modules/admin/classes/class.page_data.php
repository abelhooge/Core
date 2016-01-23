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

class PageData {

	public $module;
	public $identifier;
	public $page_path;
	public $icon;
	public $permissionGroups = array();
	public $name;
	public $priority;

    /**
     * Gets the value of module.
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Sets the value of module.
     *
     * @param mixed $module the module
     *
     * @return self
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Gets the value of identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Sets the value of identifier.
     *
     * @param mixed $identifier the identifier
     *
     * @return self
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Gets the value of page_path.
     *
     * @return mixed
     */
    public function getPagePath()
    {
        return $this->page_path;
    }

    /**
     * Sets the value of page_path.
     *
     * @param mixed $page_path the page path
     *
     * @return self
     */
    public function setPagePath($page_path)
    {
        $this->page_path = $page_path;

        return $this;
    }

    /**
     * Gets the value of icon.
     *
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the value of icon.
     *
     * @param mixed $icon the icon
     *
     * @return self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Gets the value of permissionGroups.
     *
     * @return mixed
     */
    public function getPermissionGroups()
    {
        return $this->permissionGroups;
    }

    /**
     * Sets the value of permissionGroups.
     *
     * @param mixed $permissionGroups the permission groups
     *
     * @return self
     */
    public function setPermissionGroups($permissionGroups)
    {
        $this->permissionGroups = $permissionGroups;

        return $this;
    }

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of priority.
     *
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Sets the value of priority.
     *
     * @param mixed $priority the priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }
}

?>