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
use \FuzeWorks\Events;
use \FuzeWorks\Layout;
use \FuzeWorks\EventPriority;

/**
 * Class LayoutLoadViewEventTest.
 */
class layoutLoadViewEventTest extends CoreTestAbstract
{
    /**
     * Check if the event is fired when it should be.
     */
    public function test_basic()
    {
        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'layoutLoadViewEvent', EventPriority::NORMAL);

        // And run the test
        Layout::get('home');
    }

    /**
     * Intercept and change the event.
     *
     * @expectedException \FuzeWorks\LayoutException
     */
    public function test_change()
    {
        Events::addListener(array($this, 'listener_change'), 'layoutLoadViewEvent', EventPriority::NORMAL);
        Layout::get('home');
    }

    // Change title from new to other
    public function listener_change($event)
    {

        // This controller should not exist
        $this->assertEquals('Application/Views/view.home.php', $event->file);
        $this->assertEquals('Application/Views/', $event->directory);

        // It should exist now
        $event->file = 'Application/Views/view.test.not_found';

        return $event;
    }

    /**
     * Cancel events.
     */
    public function test_cancel()
    {

        // Listen for the event and cancel it
        Events::addListener(array($this, 'listener_cancel'), 'layoutLoadViewEvent', EventPriority::NORMAL);
        $this->assertFalse(Layout::get('home'));
    }

    // Cancel all calls
    public function listener_cancel($event)
    {
        $event->setCancelled(true);
    }
}
