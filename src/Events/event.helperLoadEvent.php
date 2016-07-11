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

namespace FuzeWorks\Event;

use FuzeWorks\Event;

/**
 * Event that gets loaded when a helper is loaded.
 *
 * Use this to cancel the loading of a helper, or change the helper to be loaded
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class HelperLoadEvent extends Event
{

    /**
     * The name of the helper that gets loaded
     *
     * @var string
     */
    public $helperName;

    /**
     * The directory of the helper that gets loaded
     *
     * @var string
     */
    public $helperFile;

    /**
     * An optional extension helper name that can get loaded.
     *
     * @var string|null
     */
    public $extendedHelperName = null;

    /**
     * The directory of an optional extension helper that can get loaded
     *
     * @var string|null
     */
    public $extendedHelperFile = null;

    public function init($helperName, $helperFile, $extendedHelperName = null, $extendedHelperFile = null)
    {
        $this->helperName = $helperName;
        $this->helperFile = $helperFile;
        $this->extendedHelperName = $extendedHelperName;
        $this->extendedHelperFile = $extendedHelperFile;
    }
}
