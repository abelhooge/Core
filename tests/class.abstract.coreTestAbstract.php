<?php
/**
 * Class CoreTestAbstract
 *
 * Provides the event tests with some basic functionality
 */
abstract class CoreTestAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * This function provides the framework
     * @returns \FuzeWorks\Core
     */
    static function createCore()
    {
        $core = new FuzeWorks\Core();
        $core->init();

        //Disable debugging
        $core->mods->logger->disable();

        return $core;
    }
}