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
 * The configurator prepares FuzeWorks and loads it when requested. 
 * 
 * The user passes variables into the Configurator and the Configurator makes sure
 * that FuzeWorks is loaded accordingly. 
 * 
 * This allows for more flexible startups.
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @author    David Grudl <https://davidgrudl.com> 
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Configurator
{
    /**
     * The parameters that will be passed to FuzeWorks.
     *
     * @var array
     */ 
    protected $parameters;

    const COOKIE_SECRET = 'fuzeworks-debug';

    /**
     * Constructs the Configurator class. 
     * 
     * Loads the default parameters
     * @return void
     */
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
            'debugMode' => $debugMode
        ];
    }

    /**
     * Sets path to temporary directory.
     * @return self
     */
    public function setLogDirectory($path)
    {
        $this->parameters['logDir'] = $path;
        return $this;
    }

    /**
     * Sets the default timezone.
     * @return self
     */
    public function setTimeZone($timezone)
    {
        date_default_timezone_set($timezone);
        @ini_set('date.timezone', $timezone); // @ - function may be disabled
        return $this;
    }

    /**
     * Adds new parameters. The %params% will be expanded.
     * @return self
     */
    public function setParameters(array $params)
    {
        foreach ($params as $key => $value) {
            $this->parameters[$key] = $value;
        }
        return $this;
    }

    /**
     * Sets path to temporary directory.
     * @return self
     */
    public function setTempDirectory($path)
    {
        $this->parameters['tempDir'] = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->parameters['debugMode'];
    }

    /**
     * Set parameter %debugMode%.
     * @param  bool|string|array
     * @return self
     */
    public function setDebugMode($value)
    {
        if (is_string($value) || is_array($value)) {
            $value = static::detectDebugMode($value);
        } elseif (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Value must be either a string, array, or boolean, %s given.', gettype($value)));
        }
        $this->parameters['debugMode'] = $value;
        return $this;
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

    /**
     * Create the container which holds FuzeWorks.
     *
     * Due to the static nature of FuzeWorks, this is not yet possible. 
     * When issue #101 is completed, this should be resolved. 
     *
     * @return void
     */
    public function createContainer()
    {
        // First set all the directories
        Core::$appDir = $this->parameters['appDir'];
        Core::$wwwDir = $this->parameters['wwwDir'];
        Core::$tempDir = $this->parameters['tempDir'];
        Core::$logDir = $this->parameters['logDir'];

        // Then set debug mode
        if ($this->parameters['debugMode'])
        {
            define('ENVIRONMENT', 'DEVELOPMENT');
        }
        else
        {
            define('ENVIRONMENT', 'PRODUCTION');
        }

        // Then load the framework
        Core::init();
    }
}