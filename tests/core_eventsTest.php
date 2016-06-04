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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */
use FuzeWorks\Router;
use FuzeWorks\Events;
use FuzeWorks\EventPriority;

/**
 * Class EventTest.
 *
 * This test will test Events
 */
class eventsTest extends CoreTestAbstract
{
    public function testFireEvent()
    {
        $mock = $this->getMock('MockEventListener', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'mockEvent', EventPriority::NORMAL);
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testFireEvent
     */
    public function testObjectEvent()
    {
        $event = $this->getMock('MockEvent');

        $listener = $this->getMock('MockEventListener', array('mockListener'));
        $listener->expects($this->once())->method('mockListener')->with($this->equalTo($event));

        Events::addListener(array($listener, 'mockListener'), get_class($event), EventPriority::NORMAL);
        Events::fireEvent($event);
    }

    /**
     * @depends testObjectEvent
     */
    public function testVariablePassing()
    {
        $event = $this->getMock('MockEvent');
        $event->key = 'value';

        $eventName = get_class($event);

        Events::addListener(function($event) {
            $this->assertEquals('value', $event->key);

        }, $eventName, EventPriority::NORMAL);

        Events::fireEvent($event);
    }

    /**
     * @depends testVariablePassing
     */
    public function testVariableChanging()
    {
        // First prepare the event
        $event = $this->getMock('MockEvent');
        $event->key = 1;

        $eventName = get_class($event);

        // The first listener, should be called first due to HIGH priority
        Events::addListener(function($event) {
            $this->assertEquals(1, $event->key);
            $event->key = 2;
            return $event;

        }, $eventName, EventPriority::HIGH);

        // The second listener, should be called second due to LOW priority
        Events::addListener(function($event) {
            $this->assertEquals(2, $event->key);
            $event->key = 3;
            return $event;

        }, $eventName, EventPriority::LOW);

        // Fire the event and test if the key is the result of the last listener
        Events::fireEvent($event);
        $this->assertEquals(3, $event->key);
    }

    /**
     * @depends testFireEvent
     */
    public function testRemoveListener()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMock('MockEventListener', array('mockListener'));
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Now try and remove it
        Events::removeListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    public function testDisable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMock('MockEventListener', array('mockListener'));
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Disable the event syste,
        Events::disable();

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    public function testReEnable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMock('MockEventListener', array('mockListener'));
        $listener->expects($this->once())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Disable the event syste,
        Events::disable();

        // And now fire the event
        Events::fireEvent('mockEvent');

        // Re-enable it
        Events::enable();

        // And fire it again, this time expecting to hit the listener
        Events::fireEvent('mockEvent');
    }
}
