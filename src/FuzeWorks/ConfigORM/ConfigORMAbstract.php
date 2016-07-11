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

namespace FuzeWorks\ConfigORM;
use Iterator;

/**
 * Abstract ConfigORM class.
 *
 * This class implements the iterator, so a config file can be accessed using foreach.
 * A file can also be returned using toArray(), so it will be converted to an array
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @todo      Implement unit tests
 */
abstract class ConfigORMAbstract implements Iterator
{
    /**
     * The original state of a config file. Can be reverted to using revert().
     *
     * @var StdObject Config file
     */
    protected $originalCfg;

    /**
     * The current state of a config file.
     *
     * @var StdObject Config file
     */
    protected $cfg;

    /**
     * Revert to the original conditions of the config file.
     */
    public function revert()
    {
        $this->cfg = $this->originalCfg;
    }

    /**
     * Checks if a requested key is set in the config file.
     *
     * @param string $name Parameter name
     *
     * @return bool true on isset, false on not
     */
    public function __isset($name)
    {
        return isset($this->cfg[$name]);
    }

    /**
     * Return a value from a config file.
     *
     * @param string $name Key of the requested entry
     *
     * @return mixed Value of the requested entry
     */
    public function __get($name)
    {
        return $this->cfg[$name];
    }

    /**
     * Sets an entry in the config file.
     *
     * @param string $name  Key of the entry
     * @param mixed  $value Value of the entry
     */
    public function __set($name, $value)
    {
        $this->cfg[$name] = $value;
    }

    /**
     * Unset a value in a config file.
     *
     * @param string Key of the entry
     */
    public function __unset($name)
    {
        unset($this->cfg[$name]);
    }

    /**
     * Iterator method.
     */
    public function rewind()
    {
        return reset($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function current()
    {
        return current($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function key()
    {
        return key($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function next()
    {
        return next($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function valid()
    {
        return key($this->cfg) !== null;
    }

    /**
     * Returns the config file as an array.
     *
     * @return array Config file
     */
    public function toArray()
    {
        return $this->cfg;
    }
}