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
 * Class HelperTest.
 *
 * Helpers testing suite, will test basic loading of Helpers
 */
class helperTest extends CoreTestAbstract
{

	protected $helpers;

	public function setUp()
	{
		$factory = Factory::getInstance();
		$this->helpers = $factory->getHelpers();
	}

    public function testGetHelpersClass()
    {
        $this->assertInstanceOf('FuzeWorks\Helpers', $this->helpers);
    }

    public function testLoadHelper()
    {
    	// First test if the function/helper is not loaded yet
    	$this->assertFalse(function_exists('testHelperFunction'));

    	// Test if the helper is properly loaded
    	$this->assertTrue($this->helpers->load('test', 'tests/helpers/testLoadHelper/'));

    	// Test if the function exists now
    	$this->assertTrue(function_exists('testHelperFunction'));
    }

    /**
     * @expectedException FuzeWorks\HelperException
     */
    public function testAddHelperPathFail()
    {
    	// First test if the function is not loaded yet
    	$this->assertFalse(function_exists('testAddHelperPathFunction'));

    	// Now test if the helper can be loaded (hint: it can not)
    	$this->helpers->load('testAddHelperPath');
    }

    /**
     * @depends testAddHelperPathFail
     */
    public function testAddHelperPath()
    {
    	// Add the helperPath
    	$this->helpers->addHelperPath('tests/helpers/testAddHelperPath');

    	// And try to load it again
    	$this->assertTrue($this->helpers->load('testAddHelperPath'));

    	// And test if the function is loaded
    	$this->assertTrue(function_exists('testAddHelperPathFunction'));
    }

    public function testRemoveHelperPath()
    {
    	// Test if the path does NOT exist
    	$this->assertFalse(in_array('tests/helpers/testRemoveHelperPath', $this->helpers->getHelperPaths()));

    	// Add it
    	$this->helpers->addHelperPath('tests/helpers/testRemoveHelperPath');

    	// Assert if it's there
    	$this->assertTrue(in_array('tests/helpers/testRemoveHelperPath', $this->helpers->getHelperPaths()));

    	// Remove it
    	$this->helpers->removeHelperPath('tests/helpers/testRemoveHelperPath');

    	// And test if it's gone again
    	$this->assertFalse(in_array('tests/helpers/testRemoveHelperPath', $this->helpers->getHelperPaths()));
    }
}
