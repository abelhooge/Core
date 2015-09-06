<?php
use FuzeWorks\Events;

/**
 * Class CoreTestAbstract
 *
 * Provides the event tests with some basic functionality
 */
abstract class CoreTestAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * Remove all listeners before the next test starts
     */
    public function tearDown(){

        Events::$listeners = array();
    }
}