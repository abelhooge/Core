<?php
/**
 * Class CoreTest
 *
 * Core testing suite, will test basic core functionality
 */
class CoreTest extends CoreTestAbstract
{
	public function testCanLoadStartupFiles(){

		$core = $this->createCore();

		// Assert
		$this->assertInstanceOf('\FuzeWorks\Config', $core->mods->config);
		$this->assertInstanceOf('\FuzeWorks\Logger', $core->mods->logger);
		$this->assertInstanceOf('\FuzeWorks\Events', $core->mods->events);
		$this->assertInstanceOf('\FuzeWorks\Layout', $core->mods->layout);
		$this->assertInstanceOf('\FuzeWorks\Models', $core->mods->models);
	}
}