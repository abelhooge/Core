<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */

use FuzeWorks\Factory;

/**
 * Class ConfigTest.
 *
 * Config testing suite, will test basic config functionality while also testing default ORM's
 */
class configTest extends CoreTestAbstract
{
	protected $config;

	public function setUp()
	{
		$factory = Factory::getInstance();
		$this->config = $factory->getConfig();
	}

	public function testGetConfigClass()
	{
		$this->assertInstanceOf('FuzeWorks\Config', $this->config);
	}

	/**
	 * @depends testGetConfigClass
	 */
	public function testLoadConfig()
	{
		$this->assertInstanceOf('FuzeWorks\ConfigORM\ConfigORM', $this->config->getConfig('main'));
	}

	/**
	 * @depends testLoadConfig
	 * @expectedException FuzeWorks\ConfigException
	 */
	public function testFileNotFound()
	{
		$this->config->getConfig('notFound');
	}

    /**
     * @expectedException FuzeWorks\ConfigException
     */
    public function testAddConfigPathFail()
    {
    	// Now test if the config can be loaded (hint: it can not)
    	$this->config->getConfig('testAddConfigPath');
    }

    /**
     * @depends testAddConfigPathFail
     */
    public function testAddConfigPath()
    {
    	// Add the configPath
    	$this->config->addConfigPath('tests/config/testAddConfigPath');

    	// And try to load it again
    	$this->assertInstanceOf('FuzeWorks\ConfigORM\ConfigORM', $this->config->getConfig('testAddConfigPath'));
    }

    public function testRemoveConfigPath()
    {
    	// Test if the path does NOT exist
    	$this->assertFalse(in_array('tests/config/testRemoveConfigPath', $this->config->getConfigPaths()));

    	// Add it
    	$this->config->addConfigPath('tests/config/testRemoveConfigPath');

    	// Assert if it's there
    	$this->assertTrue(in_array('tests/config/testRemoveConfigPath', $this->config->getConfigPaths()));

    	// Remove it
    	$this->config->removeConfigPath('tests/config/testRemoveConfigPath');

    	// And test if it's gone again
    	$this->assertFalse(in_array('tests/config/testRemoveConfigPath', $this->config->getConfigPaths()));
    }

    public function testSameConfigObject()
    {
        $config = $this->config->getConfig('testsameconfigobject', array('tests/config/testSameConfigObject'));
        $config2 = $this->config->getConfig('testsameconfigobject', array('tests/config/testSameConfigObject'));

        // First test if the objects are the same instance
        $this->assertSame($config, $config2);

        // First test the existing key
        $this->assertEquals($config->key, 'value');

        // Change it and test if it's different now
        $config->key = 'other_value';
        $this->assertEquals($config2->key, 'other_value');
    }

}
