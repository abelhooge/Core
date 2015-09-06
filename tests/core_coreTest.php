<?php

/**
 * Class CoreTest
 *
 * Core testing suite, will test basic core functionality
 */
class CoreTest extends CoreTestAbstract
{
	public function testCanLoadStartupFiles(){

		// Assert
		$this->assertTrue(class_exists('\FuzeWorks\Config'));
		$this->assertTrue(class_exists('\FuzeWorks\Logger'));
		$this->assertTrue(class_exists('\FuzeWorks\Events'));
		$this->assertTrue(class_exists('\FuzeWorks\Router'));
		$this->assertTrue(class_exists('\FuzeWorks\Layout'));
		$this->assertTrue(class_exists('\FuzeWorks\Models'));
	}
}