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
 * Class Configurator.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Configurator
{
    protected $parameters;

    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();
    }

    /**
     * @return array
     */
    protected function getDefaultParameters()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $last = end($trace);
        $debugMode = static::detectDebugMode();
        return [
            'appDir' => isset($trace[1]['file']) ? dirname($trace[1]['file']) : NULL,
            'wwwDir' => isset($last['file']) ? dirname($last['file']) : NULL,
            'debugMode' => $debugMode,
            'productionMode' => !$debugMode,
            'consoleMode' => PHP_SAPI === 'cli',
        ];
    }

    /**
     * Detects debug mode by IP address.
     * @param  string|array  IP addresses or computer names whitelist detection
     * @return bool
     */
    public static function detectDebugMode($list = NULL)
    {
        $addr = isset($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : php_uname('n');
        $secret = isset($_COOKIE[self::COOKIE_SECRET]) && is_string($_COOKIE[self::COOKIE_SECRET])
            ? $_COOKIE[self::COOKIE_SECRET]
            : NULL;
        $list = is_string($list)
            ? preg_split('#[,\s]+#', $list)
            : (array) $list;
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $list[] = '127.0.0.1';
            $list[] = '::1';
        }
        return in_array($addr, $list, TRUE) || in_array("$secret@$addr", $list, TRUE);
    }
}