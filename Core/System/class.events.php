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
 * Class Events
 *
 * FuzeWorks is built in a way that almost every core-event can be manipulated by modules. This class provides various ways to hook into the core (or other modules)
 * and manipulate the outcome of the functions. Modules and core actions can 'fire' an event and modules can 'hook' into that event. Let's take a look at the example below:
 *
 * If we want to add the current time at the end of each page title, we need to hook to the corresponding event. Those events are found in the 'events' directory in the system directory.
 * The event that will be fired when the title is changing is called layoutSetTitleEvent. So if we want our module to hook to that event, we add the following to the constructor:
 *
 * Events::addListener(array($this, "onLayoutSetTitleEvent"), "layoutSetTitleEvent", EventPriority::NORMAL);
 *
 * This will add the function "onLayoutSetTitleEvent" of our current class ($this) to the list of listeners with priority NORMAL. So we need to add
 * a method called onLayoutSetTitleEvent($event) it is very important to add the pointer-reference (&) or return the event, otherwise it doesn't change the event variables.
 *
 * If we now add the following code to our method, it will add the current time at the front of each title.
 *
 * $event->title = date('H:i:s ').$event->title;
 *
 * @package     net.techfuze.fuzeworks.core
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Events {

	public static $listeners = array();
    private static $enabled = true;
    private static $register;

    /**
     * Adds a function as listener
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param String $eventName The name of the event
     * @param int $priority The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     * @see EventPriority
     *
     * @throws EventException
     */
	public static function addListener($callback, $eventName, $priority = EventPriority::NORMAL){
        if(EventPriority::getPriority($priority) == false)
            throw new Exception("Unknown priority " . $priority);

        if(!isset(self::$listeners[$eventName]))
            self::$listeners[$eventName] = array();

        if(!isset(self::$listeners[$eventName][$priority]))
           self::$listeners[$eventName][$priority] = array();

        self::$listeners[$eventName][$priority][] = $callback;
    }

    /**
     * Removes a function as listener
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param String $eventName The name of the event
     * @param int $priority The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     * @see EventPriority
     *
     * @throws EventException
     */
    public static function removeListener($callback, $eventName, $priority = EventPriority::NORMAL){
        if(EventPriority::getPriority($priority) == false)
            throw new Exception("Unknown priority " . $priority);

        if(!isset(self::$listeners[$eventName]))
            return;

        if(!isset(self::$listeners[$eventName][$priority]))
            return;

        foreach(self::$listeners[$eventName][$priority] as $i => $_callback){

            if($_callback == $callback) {
                unset(self::$listeners[$eventName][$priority][$i]);
                return;
            }
        }
    }

	/**
     * Fires an Event
     *
     * The Event gets created, passed around and then returned to the issuer.
     * @param  Mixed  $input Object for direct event, string for system event or notifierEvent
     * @return \FuzeWorks\Event        The Event
     */
	public static function fireEvent($input) {
		if (is_string($input)) {
			// If the input is a string
			$eventClass = $input;
			$eventName = $input;
	        if(!class_exists($eventClass)){
	            // Check if the file even exists
	            $file = "Core/Events/event.".$eventName.".php";
	            if(file_exists($file)){
	                // Load the file
                    $eventClass = "\FuzeWorks\Event\\" . $eventClass;
	                require_once($file);
	            }else{
	                // No event arguments? Looks like a notify-event
	                if(func_num_args() == 1){
	                    // Load notify-event-class
	                    $eventClass = '\FuzeWorks\Event\NotifierEvent';
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

		Logger::newLevel("Firing Event: '".$eventName."'");
		Logger::log('Initializing Event');

		if (func_num_args() > 1)
			call_user_func_array(array($event, 'init'), array_slice(func_get_args(), 1));

        // Do not run if the event system is disabled
        if (!self::$enabled) {
            Logger::log("Event system is disabled");
            Logger::stopLevel();
            return $event;
        }

		Logger::log("Checking for Listeners");

        // Read the event register for listeners
        $register = self::$register;
        if (isset($register[$eventName])) {
            for ($i=0; $i < count($register[$eventName]); $i++) {
                Modules::get($register[$eventName][$i]);
            }
        }

        //There are listeners for this event
        if(isset(self::$listeners[$eventName])) {
            //Loop from the highest priority to the lowest
            for ($priority = EventPriority::getHighestPriority(); $priority <= EventPriority::getLowestPriority(); $priority++) {
                //Check for listeners in this priority
                if (isset(self::$listeners[$eventName][$priority])) {
                	$listeners = self::$listeners[$eventName][$priority];
                    Logger::newLevel('Found listeners with priority ' . EventPriority::getPriority($priority));
                    //Fire the event to each listener
                    foreach ($listeners as $callback) {
                        if (is_callable($callback)) {
                            Logger::newLevel('Firing function');
                        } elseif(!is_string($callback[0]))
                            Logger::newLevel('Firing ' . get_class($callback[0]) . '->' . $callback[1]);
                        else
                            Logger::newLevel('Firing ' . join('->', $callback));
                        try {
                            call_user_func($callback, $event);
                        } catch (ModuleException $e) {
                            Logger::exceptionHandler($e);
                        }
                        Logger::stopLevel();
                    }

                    Logger::stopLevel();
                }
            }
        }

		Logger::stopLevel();
		return $event;
	}

    /**
     * Build a register which says which module listens for what event.
     *
     * This way not all modules have to be loaded in order to listen to all events.
     * @return boolean true on success
     */
    public static function buildEventRegister() {
        $event_register = array();

        // Check wether there is data or not
        $data = Modules::$register;
        $data = null;
        if (is_null($data)) {
            self::$register = array();
            return true;
        }

        // If there is, handle it
        foreach ($data as $key => $value) {
            if (isset($value['events'])) {
                if (!empty($value['events'])) {
                    for ($i=0; $i < count($value['events']); $i++) { 
                        if (isset($event_register[$value['events'][$i]])) {
                            $event_register[$value['events'][$i]][] = $key;
                        } else {
                            $event_register[$value['events'][$i]] = array($key);
                        }
                    }
                }
            }
        }

        self::$register = $event_register;
        return true;
    }

    /**
     * Enables the event system
     */
    public static function enable() {
        Logger::log("Enabled the Event system");
        self::$enabled = true;
    }

    /**
     * Disables the event system
     */
    public static function disable() {
        Logger::log("Disabled the Event system");
        self::$enabled = false;
    }
}


?>