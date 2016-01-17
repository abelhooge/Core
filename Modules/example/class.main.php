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

namespace Module\Example;
use \FuzeWorks\Module;
use \FuzeWorks\Event;
use \FuzeWorks\EventPriority;
use \FuzeWorks\Events;
use \FuzeWorks\Logger;

/**
 * Example module.
 *
 * Use this is a reference to create new modules.
 * @package     net.techfuze.fuzeworks.example
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Main {

	use Module;

	/**
	 * Loads the module and registers the events
	 *
	 * Every main moduleclass needs an onLoad method. This method is called first before anything else and cam be used to do some global actions.
	 * @access public
	 */
	public function onLoad() {
		// Here we register an eventListener for the ExampleEvent. See ExampleListener for more info
		Events::addListener(array($this, 'exampleListener'), 'ExampleEvent', EventPriority::NORMAL);
	}

	/**
	 * Test method that can be called
	 * @return String Example text
	 */
	public function test() {
		return "It works!";
	}

	/**
	 * An example listener that introduces you to the basics of event handling
	 * @param  ExampleEvent $event  The event to listen for
	 * @return ExampleEvent         The event after it has been handled
	 */
	public function exampleListener($event) {
		Logger::log("Called the eventListener. This listener can now handle the event and change some data");
		// For this listener, we only change one variable
		$event->setVariable("New Value");

		// And then we return it
		return $event;
	}

	/**
	 * In this example we create a simple event. This event will be created, passed around and then received in the example listener.
	 */
	public function createEvent() {
		// First we log some data
		Logger::log("Now creating a test event.");

		// First we create the event object and some variables to assign to it
		$eventObject = new ExampleEvent();
		$variable = "Test Variable";

		// Then we fire the event by parsing the event object and the variables into the fireEvent function.
		$event = Events::fireEvent($eventObject, $variable);

		// Here we can read some variables from the event
		$result = $event->getVariable();

		// And now we can do things with the data. For now we just return it
		return $result;
	}

	/**
	 * Gets called when the path matches the regex of this module.
	 * @access public
	 * @param  array   Regex matches
	 * @return void
	 */
	public function route($matches = array()) {
		// Just print the inputted data:
		echo "<h3>Input data: ".$matches['data']."</h3>";
	}

}

class ExampleEvent extends Event {

	private $var1;

	public function init($variable) {
		$this->var1 = $variable;
	}

	public function getVariable() {
		return $this->var1;
	}

	public function setVariable($var) {
		$this->var1 = $var;
	}
}

?>