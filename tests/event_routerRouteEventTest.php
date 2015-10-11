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

use \FuzeWorks\Events;
use \FuzeWorks\Router;
use \FuzeWorks\EventPriority;

/**
 * Class RouterRouteEventTest
 */
class RouterRouteEventTest extends CoreTestAbstract{

    /**
     * Check if the event is fired when it should be
     */
    public function test_basic(){

        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'routerRouteEvent', EventPriority::NORMAL);
        Router::setPath('a/b/c');
        Router::route(false);
    }

    /**
     * Cancel events
     */
    public function test_cancel(){

        Router::setPath('x/y/z');

        Events::addListener(array($this, 'listener_cancel'), 'routerRouteEvent', EventPriority::NORMAL);
        Router::route(false);

        $this->assertNotEquals('x', Router::getMatches()['controller']);
        $this->assertNotEquals('y', Router::getMatches()['function']);
        $this->assertNotEquals('z', Router::getMatches()['parameters']);
    }

    // Cancel all calls
    public function listener_cancel($event){

        $event->setCancelled(true);
    }
}