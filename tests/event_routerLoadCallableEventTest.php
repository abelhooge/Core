<?php
/**
 * Class RouterLoadCallableEventTest
 */
class RouterLoadCallableEventTest extends CoreTestAbstract{

    /**
     * Check if the event is fired when it should be
     */
    public function test_basic(){

        $core = $this->createCore();

        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod')->with(
            $this->isInstanceOf('\routerLoadCallableEvent')
        );

        $core->mods->events->addListener(array($mock, 'mockMethod'), 'routerLoadCallableEvent', \FuzeWorks\EventPriority::NORMAL);
        //Prevent ouputting HTML
        ob_start();
        $core->mods->router->route();
        ob_end_clean();
    }

    /**
     * Intercept and change
     * @todo Make this test correct
     */
    /*public function test_change(){

        $core = $this->createCore();

        $core->mods->events->addListener(array($this, 'listener_change'), 'routerLoadCallableEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('x/y/z');
        ob_start();
        $core->mods->router->route(true);
        ob_end_clean();

        $this->assertNotNull($core->mods->router->getCallable());
        $this->assertInstanceOf('\FuzeWorks\Router', $core->mods->router->getCallable()[0]);
    }*/

    // Change title from new to other
    public function listener_change(\routerLoadCallableEvent $event){

        // This controller should not exist
        $this->assertEquals('x', $event->controller);
        $this->assertEquals('y', $event->function);

        // It should exist now
        $event->controller  = 'home';
        $event->function    = 'index';
    }

    /**
     * Cancel events
     */
    public function test_cancel(){

        // When the callable may execute, the callable will change to the controller
        // (because '' will trigger the default callable')
        $core = $this->createCore();
        $core->mods->router->setPath('');

        $core->mods->events->addListener(array($this, 'listener_cancel'), 'routerLoadCallableEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->route();
        $this->assertTrue(is_callable($core->mods->router->getCallable()));

        // When disabled, the default controller will be loaded and the callable will be overwritten
        $core = $this->createCore();
        $core->mods->router->setPath('');
        $core->mods->router->route();
        $this->assertFalse(is_callable($core->mods->router->getCallable()));
    }

    // Cancel all calls
    public function listener_cancel(\routerLoadCallableEvent $event){

        $event->setCancelled(true);
    }
}