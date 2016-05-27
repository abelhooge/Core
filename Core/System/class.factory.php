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
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Factory
{

	private static $sharedFactoryInstance;

	private $instances = array();

	public function __construct()
	{
		self::$sharedFactoryInstance = $this;
        $this->instances['Config'] = new Config();
        $this->instances['Logger'] = new Logger();
        $this->instances['Events'] = new Events();
        $this->instances['Models'] = new Models();
        $this->instances['Layout'] = new Layout();
        $this->instances['Modules'] = new Modules();
        $this->instances['Libraries'] = new Libraries();
        $this->instances['Helpers'] = new Helpers();
        $this->instances['Database'] = new Database();
        $this->instances['Language'] = new Language();
        $this->instances['Utf8'] = new Utf8();
        $this->instances['URI'] = new URI();
        $this->instances['Security'] = new Security();
        $this->instances['Input'] = new Input();
        $this->instances['Router'] = new Router();
        $this->instances['Output'] = new Output();
	}

	public function newInstance($className, $namespace = 'FuzeWorks\\')
	{
		$instanceName = ucfirst($className);
		$className = $namespace.$instanceName;
		$this->instances[$instanceName] = new $className();
	}

	public static function getInstance($cloneInstance = true)
	{	
		if ($cloneInstance === true)
		{
			return clone self::$sharedFactoryInstance;
		}

		return self::$sharedFactoryInstance;
	}

	public function getConfig()
	{
		return $this->instances['Config'];
	}

	public function getCore()
	{
		return $this->instances['Core'];
	}

	public function getDatabase()
	{
		return $this->instances['Database'];
	}

	public function getEvents()
	{
		return $this->instances['Events'];
	}

	public function getHelpers()
	{
		return $this->instances['Helpers'];
	}

	public function getInput()
	{
		return $this->instances['Input'];
	}

	public function getLanguage()
	{
		return $this->instances['Language'];
	}

	public function getLayout()
	{
		return $this->instances['Layout'];
	}

	public function getLibraries()
	{
		return $this->instances['Config'];
	}

	public function getLogger()
	{
		return $this->instances['Logger'];
	}

	public function getModels()
	{
		return $this->instances['Models'];
	}
	
	public function getModules()
	{
		return $this->instances['Modules'];
	}

	public function getOutput()
	{
		return $this->instances['Output'];
	}

	public function getRouter()
	{
		return $this->instances['Router'];
	}

	public function getSecurity()
	{
		return $this->instances['Security'];
	}

	public function getUri()
	{
		return $this->instances['URI'];
	}

	public function getUtf8()
	{
		return $this->instances['Utf8'];
	}


}
