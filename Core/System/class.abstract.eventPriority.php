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

namespace FuzeWorks;

/**
 * Class EventPriority.
 *
 * The EventPriority is an "enum" which gives priorities an integer value, the higher the integer value, the lower the
 * priority. The available priorities are, from highest to lowest:
 *
 * EventPriority::MONITOR
 * EventPriority::HIGHEST
 * EventPriority::HIGH
 * EventPriority::NORMAL
 * EventPriority::LOW
 * EventPriority::LOWEST
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
abstract class EventPriority
{
    const LOWEST = 5;
    const LOW = 4;
    const NORMAL = 3;
    const HIGH = 2;
    const HIGHEST = 1;
    const MONITOR = 0;

    /**
     * Returns the string of the priority based on the integer.
     *
     * @param $intPriorty
     *
     * @return bool|string A bool when the integer isn't a priority. If the integer is a priority, the name is returned
     */
    public static function getPriority($intPriorty)
    {
        switch ($intPriorty) {
            case 5:
                return 'EventPriority::LOWEST';
            case 4:
                return 'EventPriority::LOW';
            case 3:
                return 'EventPriority::NORMAL';
            case 2:
                return 'EventPriority::HIGH';
            case 1:
                return 'EventPriority::HIGHEST';
            case 0:
                return 'EventPriority::MONITOR';
            default:
                return false;
        }
    }

    /**
     * Returns the highest priority
     * This function is needed for the firing of events in the right order,.
     *
     * @return int
     */
    public static function getHighestPriority()
    {
        return self::MONITOR;
    }

    /**
     * Returns the lowest priority
     * This function is needed for the firing of events in the right order,.
     *
     * @return int
     */
    public static function getLowestPriority()
    {
        return self::LOWEST;
    }
}
