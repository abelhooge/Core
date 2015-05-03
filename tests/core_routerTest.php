<?php
/**
 * Class RouterTest
 *
 * This test will test the router
 */
class RouterTest extends CoreTestAbstract
{
    public function testParsePath(){

        $core = $this->createCore();

        // Act and assert
        $core->mods->router->setPath('a/b/c/d/');
        $this->assertEquals('a/b/c/d', $core->mods->router->getPath());

        $core->mods->router->setPath('//a//b//c');
        $this->assertEquals('a/b/c', $core->mods->router->getPath());

        $core->mods->router->setPath('/');
        $this->assertEquals('', $core->mods->router->getPath());

        $core->mods->router->setPath('');
        $this->assertEquals('', $core->mods->router->getPath());

        $core->mods->router->setPath(false);
        $this->assertEquals('', $core->mods->router->getPath());

        $core->mods->router->setPath(null);
        $this->assertEquals('', $core->mods->router->getPath());
    }

    /**
     * @depends testParsePath
     */
    public function testDoRoute(){

        $core = $this->createCore();

        // Act
        $core->mods->router->setPath('a/b/c/d/');
        $core->mods->router->route(false);

        // Assert
        // Whole route
        $this->assertEquals(array('a','b',array('c','d')), array($core->mods->router->getController(), $core->mods->router->getFunction(), $core->mods->router->getParameters()));
        $this->assertEquals('a', $core->mods->router->getController());
        $this->assertEquals('d', $core->mods->router->getParameter(-1));
        $this->assertEquals(null, $core->mods->router->getParameter(5));

        // Parameters
        $this->assertEquals(array('c','d'), $core->mods->router->getParameters());
        $this->assertEquals('c', $core->mods->router->getParameter(0));
        $this->assertEquals('d', $core->mods->router->getParameter(-1));

        // Function and controller
        $this->assertEquals('a', $core->mods->router->getController());
        $this->assertEquals('b', $core->mods->router->getFunction());
    }

    /**
     * @depends testDoRoute
     */
    public function testOddRoutes(){

        $core = $this->createCore();

        // Empty path
        $core->mods->router->setPath(null);
        $core->mods->router->route(false);
        $this->assertEquals(null, $core->mods->router->getController());

        // Double slashes
        $core->mods->router->setPath('a///b');
        $core->mods->router->route(false);
        $this->assertEquals(array('a','b'), array($core->mods->router->getController(), $core->mods->router->getFunction()));

        // Escaped path path
        $core->mods->router->setPath('/a\/b\/c/');
        $core->mods->router->route(false);
        $this->assertEquals(array('a\\','b\\','c'), array($core->mods->router->getController(), $core->mods->router->getFunction(), $core->mods->router->getParameter(0)));
        $this->assertNotEquals('a', $core->mods->router->getController());
    }

    public function testCustomRoute(){

        $core = $this->createCore();

        $core->mods->router->addRoute('/test1/test2/', 'callable');
        $this->assertArraySubset(array('/test1/test2/' => 'callable'), $core->mods->router->getRoutes());

        $core->mods->router->setPath('test1/test2');
        $core->mods->router->route(false);
        $this->assertEquals(array('test1', 'test2'), array($core->mods->router->getController(), $core->mods->router->getFunction()));

    }

    public function testCustomRouteWithParameters(){

        $core = $this->createCore();

        $core->mods->router->addRoute('/^b\/(?P<controller>[^\/]+)\/?(?P<function>.+?)$/', 'callable');
        $core->mods->router->addRoute('/e\/(?P<function>[^\/]+)/', 'callable');
        $core->mods->router->addRoute('/b\/b$/', 'callable');

        $core->mods->router->setPath('b/controller_a/function_a');
        $core->mods->router->route(false);
        $this->assertEquals('controller_a', $core->mods->router->getController());
        $this->assertEquals('function_a', $core->mods->router->getFunction());

        $core->mods->router->setPath('e/function_b/c');
        $core->mods->router->route(false);
        $this->assertEquals(null, $core->mods->router->getController());
        $this->assertEquals('function_b', $core->mods->router->getFunction());

        $core->mods->router->setPath('b/b');
        $core->mods->router->route(false);
        $this->assertEquals(null, $core->mods->router->getController());
        $this->assertEquals(null, $core->mods->router->getFunction());

        $core->mods->router->setPath('a/b');
        $core->mods->router->route(false);
        $this->assertEquals('a', $core->mods->router->getController());
        $this->assertEquals('b', $core->mods->router->getFunction());
    }
}