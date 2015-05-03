<?php
/**
 * Class RouterSetPathEventTest
 */
class RouterSetPathEventTest extends CoreTestAbstract{

    /**
     * Check if the event is fired when it should be
     */
    public function testRouterSetPathEvent(){

        $core = $this->createCore();

        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod')->with(
            $this->isInstanceOf('\routerSetPathEvent')
        );

        $core->mods->events->addListener(array($mock, 'mockMethod'), 'routerSetPathEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('a/b/c');
    }

    /**
     * Intercept and change
     */
    public function testRouterSetPathEvent_change(){

        $core = $this->createCore();

        $core->mods->events->addListener(array($this, 'listener_change'), 'routerSetPathEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('a/b/c');

        $this->assertEquals('x/y/z', $core->mods->router->getPath());
    }

    // Change title from new to other
    public function listener_change(\routerSetPathEvent $event){

        $this->assertEquals('a/b/c', $event->path);
        $event->path = 'x/y/z';
    }

    /**
     * Cancel events
     */
    public function testLayoutFunctionCallEvent_cancel(){

        $core = $this->createCore();
        $core->mods->router->setPath('a/b/c');

        $core->mods->events->addListener(array($this, 'listener_cancel'), 'routerSetPathEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('x/y/z');

        $this->assertEquals('a/b/c', $core->mods->router->getPath());
    }

    // Cancel all calls
    public function listener_cancel(\routerSetPathEvent $event){

        $event->setCancelled(true);
    }


    /**
     * Do not cancel events
     */
    public function testLayoutFunctionCallEvent_dontcancel(){

        $core = $this->createCore();
        $core->mods->router->setPath('a/b/c');

        $core->mods->events->addListener(array($this, 'listener_dontcancel'), 'routerSetPathEvent', \FuzeWorks\EventPriority::NORMAL);
        $core->mods->router->setPath('x/y/z');

        $this->assertEquals('x/y/z', $core->mods->router->getPath());
    }

    // Cancel all calls
    public function listener_dontcancel(\routerSetPathEvent $event){

        $event->setCancelled(false);
    }
}