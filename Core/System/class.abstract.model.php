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

namespace FuzeWorks;

/**
 * Interface for a Module that gives abstract model types
 * A model server must contain the methods from this interface in order to correctly serve models
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
interface ModelServer {
	public function giveModel($type);
}

/**
 * Abstract class Model
 *
 * Abstract for a model data representation, loads the correct parent type
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
abstract class Model {

	/**
	 * The parent class holder object
	 * Requests get redirected to this class
	 * @access private
	 * @var Parent Object
	 */
	private $parentClass;

	/**
	 * Set the type of this model. Eg, use techfuze/databasemodel and Databasemodel to get a SQL connected model
	 * @access protected
	 * @param String Module_name, the name of the module where the model can be found
	 * @param String Model_type, model type to return
	 */
	protected function setType($module_name, $model_type) {
		$mod = Modules::get($module_name);
		$this->parentClass = $mod->giveModel($model_type);
	}

	/**
	 * Retrieves a value from the model class
	 * @access public
	 * @param Any key
	 * @return Any value from the model class
	 */
	public function __get($name) {
		return $this->parentClass->$name;
	}

	/**
	 * Sets a value in the model class
	 * @access public
	 * @param Any key
	 * @param Any value
	 */
	public function __set($name, $value) {
		$this->parentClass->$name = $value;
	}

	/**
	 * Calls a function in the model class
	 * @access public
	 * @param String function_name
	 * @param Array values
	 * @return Function return
	 */
	public function __call($name, $params) {
		return call_user_func_array(array($this->parentClass, $name), $params);
	}
}