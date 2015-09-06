<?php
use \FuzeWorks\Core;
use \FuzeWorks\Router;

/**
 * Class RouterTest
 *
 * This test will test the router
 */
class RouterTest extends CoreTestAbstract
{
    public function testParsePath(){

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
    public function testDoRoute(){

        // Act
        Router::setPath('a/b/c/d/');
        Router::route(false);

        // Assert
        // Whole route
        $this->assertEquals(array('a','b',array('c','d')), array(Router::getController(), Router::getFunction(), Router::getParameters()));
        $this->assertEquals('a', Router::getController());
        $this->assertEquals('d', Router::getParameter(-1));
        $this->assertEquals(null, Router::getParameter(5));

        // Parameters
        $this->assertEquals(array('c','d'), Router::getParameters());
        $this->assertEquals('c', Router::getParameter(0));
        $this->assertEquals('d', Router::getParameter(-1));

        // Function and controller
        $this->assertEquals('a', Router::getController());
        $this->assertEquals('b', Router::getFunction());
    }

    /**
     * @depends testDoRoute
     */
    public function testOddRoutes(){

        // Empty path
        Router::setPath(null);
        Router::route(false);
        $this->assertEquals(null, Router::getController());

        // Double slashes
        Router::setPath('a///b');
        Router::route(false);
        $this->assertEquals(array('a','b'), array(Router::getController(), Router::getFunction()));

        // Escaped path path
        Router::setPath('/a\/b\/c/');
        Router::route(false);
        $this->assertEquals(array('a\\','b\\','c'), array(Router::getController(), Router::getFunction(), Router::getParameter(0)));
        $this->assertNotEquals('a', Router::getController());
    }

    public function testCustomRoute(){

        Router::addRoute('/test1\/test2/', 'callable');
        $this->assertArraySubset(array('/test1\/test2/' => 'callable'), Router::getRoutes());
    }

    public function testCustomRouteWithParameters(){

        Router::addRoute('/^b\/(?P<controller>[^\/]+)\/?(?P<function>.+?)$/', 'callable');
        Router::addRoute('/e\/(?P<function>[^\/]+)/', 'callable');
        Router::addRoute('/b\/b$/', 'callable');

        Router::setPath('b/controller_a/function_a');
        Router::route(false);
        $this->assertEquals('controller_a', Router::getController());
        $this->assertEquals('function_a', Router::getFunction());

        Router::setPath('e/function_b/c');
        Router::route(false);
        $this->assertEquals(null, Router::getController());
        $this->assertEquals('function_b', Router::getFunction());

        Router::setPath('b/b');
        Router::route(false);
        $this->assertEquals(null, Router::getController());
        $this->assertEquals(null, Router::getFunction());

        Router::setPath('a/b');
        Router::route(false);
        $this->assertEquals('a', Router::getController());
        $this->assertEquals('b', Router::getFunction());
    }
}