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
use \FuzeWorks\Router;
use \FuzeWorks\EventPriority;

/**
 * Class RouterLoadCallableEventTest.
 */
class routerLoadCallableEventTest extends CoreTestAbstract
{
    /**
     * Check if the event is fired when it should be.
     */
    public function test_basic()
    {
        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod');

        Router::setPath('/');
        Events::addListener(function ($event) {
            $event->setCancelled(true);

        }, 'layoutLoadViewEvent', EventPriority::HIGHEST);
        Events::addListener(array($mock, 'mockMethod'), 'routerLoadCallableEvent', EventPriority::NORMAL);

        //Prevent ouputting HTML
        ob_start();
        Router::route();
        ob_end_clean();
    }

    /**
     * Intercept and change.
     */
    public function test_change()
    {
        Events::addListener(function ($event) {
            $event->setCancelled(true);

        }, 'layoutLoadViewEvent', EventPriority::HIGHEST);
        Events::addListener(array($this, 'listener_change'), 'routerLoadCallableEvent', EventPriority::NORMAL);
        Router::setPath('x/y/z');
        Router::route(true);

        Events::$listeners = array();
        Events::addListener(function ($event) {
            $event->setCancelled(true);

        }, 'layoutLoadViewEvent', EventPriority::HIGHEST);
        Events::addListener(array($this, 'listener_change'), 'routerLoadCallableEvent', EventPriority::NORMAL);
        Router::setPath('x/y/z');
        Router::route(true);

        $this->assertNotNull(Router::getCallable());
        $this->assertInstanceOf('\Application\Controller\Standard', Router::getCallable());
    }

    // Change title from new to other
    public function listener_change($event)
    {

        // This controller should not exist
        $this->assertEquals('x', $event->matches['controller']);
        $this->assertEquals('y', $event->matches['function']);

        // It should exist now
        $event->matches['controller'] = 'standard';
        $event->matches['function'] = 'index';

        return $event;
    }

    /**
     * Cancel events.
     */
    public function test_cancel()
    {
        ob_start();
        // When the callable may execute, the callable will change to the controller
        // (because '' will trigger the default callable)
        Router::setPath('');
        Events::addListener(array($this, 'listener_cancel'), 'routerLoadCallableEvent', EventPriority::NORMAL);
        Router::route();

        $this->assertTrue(is_callable(Router::getCallable()));

        // When disabled, the default controller will be loaded and the callable will be overwritten
        // Remove the listener
        Events::$listeners = array();
        Events::addListener(function ($event) {
            $event->setCancelled(true);

        }, 'layoutLoadViewEvent', EventPriority::HIGHEST);

        Router::route();
        $this->assertFalse(is_callable(Router::getCallable()));
        ob_end_clean();
    }

    // Cancel all calls
    public function listener_cancel($event)
    {
        $event->setCancelled(true);
    }
}
