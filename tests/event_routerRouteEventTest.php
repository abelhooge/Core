<?php
/**
 * Class RouterRouteEventTest
 */
class RouterRouteEventTest extends CoreTestAbstract{

    /**
     * Check if the event is fired when it should be
     */
    public function test_basic(){

        $core = $this->createCore();

        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod')->with(
            $this->isInstanceOf('\routerRouteEvent')
        );

        $core->mods->events->addListener(array($mock, 'mockMethod'), 'routerRouteEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('a/b/c');
        $core->mods->router->route(false);
    }

    /**
     * Cancel events
     */
    public function test_cancel(){

        $core = $this->createCore();
        $core->mods->router->setPath('a/b/c');

        $core->mods->events->addListener(array($this, 'listener_cancel'), 'routerRouteEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->route(false);

        $this->assertNotEquals('a', $core->mods->router->getController());
        $this->assertNotEquals('b', $core->mods->router->getFunction());
        $this->assertNotEquals(array('c'), $core->mods->router->getParameters());
    }

    // Cancel all calls
    public function listener_cancel(\System\Events\routerRouteEvent $event){

        $event->setCancelled(true);
    }
}