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
use \FuzeWorks\Router;

/**
 * Class RouterTest.
 *
 * This test will test the router
 */
class routerTest extends CoreTestAbstract
{
    public function testParsePath()
    {

        // Act and assert
        Router::setPath('a/b/c/d/');
        $this->assertEquals('a/b/c/d', Router::getPath());

        Router::setPath('//a//b//c');
        $this->assertEquals('a/b/c', Router::getPath());

        Router::setPath('/');
        $this->assertEquals('', Router::getPath());

        Router::setPath('');
        $this->assertEquals('', Router::getPath());

        Router::setPath(false);
        $this->assertEquals('', Router::getPath());

        Router::setPath(null);
        $this->assertEquals('', Router::getPath());
    }

    /**
     * @depends testParsePath
     */
    public function testDoRoute()
    {

        // Act
        Router::setPath('a/b/c/d/');
        Router::route(false);

        // Assert
        // Whole route
        $this->assertEquals(array('a', 'b', 'c/d'), array(Router::getMatches()['controller'], Router::getMatches()['function'], Router::getMatches()['parameters']));
        $this->assertEquals('a', Router::getMatches()['controller']);

        // Parameters
        $this->assertEquals('c/d', Router::getMatches()['parameters']);

        // Function and controller
        $this->assertEquals('a', Router::getMatches()['controller']);
        $this->assertEquals('b', Router::getMatches()['function']);
    }

    /**
     * @depends testDoRoute
     */
    public function testOddRoutes()
    {

        // Empty path
        Router::setPath(null);
        Router::route(false);
        $this->assertEquals(null, Router::getMatches()['controller']);

        // Double slashes
        Router::setPath('a///b');
        Router::route(false);
        $this->assertEquals(array('a', 'b'), array(Router::getMatches()['controller'], Router::getMatches()['function']));

        // Escaped path path
        Router::setPath('/a\/b\/c/');
        Router::route(false);
        $this->assertEquals(array('a\\', 'b\\', 'c'), array(Router::getMatches()['controller'], Router::getMatches()['function'], Router::getMatches()['parameters']));
        $this->assertNotEquals('a', Router::getMatches()['controller']);
    }

    public function testCustomRoute()
    {
        Router::addRoute('/test1\/test2/', 'callable');
        $this->assertArraySubset(array('/test1\/test2/' => 'callable'), Router::getRoutes());
    }

    public function testCustomRouteWithParameters()
    {
        Router::addRoute('/^b\/(?P<controller>[^\/]+)\/?(?P<function>.+?)$/', 'callable');
        Router::addRoute('/e\/(?P<function>[^\/]+)/', 'callable');
        Router::addRoute('/b\/b$/', 'callable');

        Router::setPath('b/controller_a/function_a');
        Router::route(false);
        $this->assertEquals('controller_a', Router::getMatches()['controller']);
        $this->assertEquals('function_a', Router::getMatches()['function']);

        Router::setPath('e/function_b/c');
        Router::route(false);
        $this->assertFalse(isset(Router::getMatches()['controller']));
        $this->assertEquals('function_b', Router::getMatches()['function']);

        Router::setPath('b/b');
        Router::route(false);
        $this->assertFalse(isset(Router::getMatches()['controller']));
        $this->assertFalse(isset(Router::getMatches()['function']));

        Router::setPath('a/b');
        Router::route(false);
        $this->assertEquals('a', Router::getMatches()['controller']);
        $this->assertEquals('b', Router::getMatches()['function']);
    }
}
