<?php
use \FuzeWorks\Core;
use \FuzeWorks\Events;
use \FuzeWorks\EventPriority;

/**
 * Class CoreStartEventTest
 */
class CoreStartEventTest extends CoreTestAbstract
{
    /**
     * Check if the event is fired when it should be
     */
    public function testCoreStartEvent(){

        $mock = $this->getMock('MockEvent', array('mockMethod'));
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'coreStartEvent', EventPriority::NORMAL);
        Core::init();
    }
}