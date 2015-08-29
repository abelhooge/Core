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
 * Catcher Class
 *
 * This class catches requests and returns nothing. Handy for a temporary replacement object
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Catcher extends Bus{

	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function __get($name) {
		return new Catcher($this->core);
	}

	public function __set($name, $value) {}

	public function __unset($name) {}

	public function __isset($name) {}

	public function __call($name, $params) {
		return new Catcher($this->core);
	}

	public static function __callStatic($name, $params) {}

	public function __sleep() {}

	public function __wakeup() {}

	public function __toString() {}

}

/**
 * Events catcher class.
 *
 * Used for replacing the events core module
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class EventsCatcher extends Catcher {
	public function __construct(&$core) {
		parent::__construct($core);
	}

	public function fireEvent($input) {
		if (is_string($input)) {
			// If the input is a string
			$eventClass = $input;
			$eventName = $input;
	        if(!class_exists($eventClass)){
	            // Check if the file even exists
	            $file = "Core/Events/event.".$eventName.".php";
	            if(file_exists($file)){
	                // Load the file
	                require_once($file);
	            }else{
	                // No event arguments? Looks like a notify-event
	                if(func_num_args() == 1){
	                    // Load notify-event-class
	                    $eventClass = '\FuzeWorks\NotifierEvent';
	                }else{
	                    // No notify-event: we tried all we could
	                    throw new Exception("Event ".$eventName." could not be found!");
	                }
	            }
	        }

	        $event = new $eventClass($this);
		} elseif (is_object($input)) {
			$eventName = get_class($input);
			$eventName = explode('\\', $eventName);
			$eventName = end($eventName);
			$event = $input;
		} else {
			// INVALID EVENT
			return false;
		}

		if (func_num_args() > 1)
			call_user_func_array(array($event, 'init'), array_slice(func_get_args(), 1));

		return $event;
	}
}


?>