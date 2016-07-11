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
 * Class LibraryTest.
 *
 * Libraries testing suite, will test basic loading of and management of Libraries
 */
class libraryTest extends CoreTestAbstract
{

    protected $libraries;

    public function setUp()
    {
        $factory = Factory::getInstance();
        $this->libraries = $factory->libraries;
    }

    public function testGetLibrariesClass()
    {
        $this->assertInstanceOf('FuzeWorks\Libraries', $this->libraries);
    }

    /**
     * @depends testGetLibrariesClass
     */
    public function testLoadBasicLibrary()
    {
        // Simple test of loading a library and checking if it exists
        $this->assertInstanceOf('Application\Library\TestLoadBasicLibrary', 
            $this->libraries->get('TestLoadBasicLibrary', null, array('tests/libraries/testLoadBasicLibrary')));
    }

    /**
     * @depends testLoadBasicLibrary
     */
    public function testLoadExtendedLibrary()
    {
        // Load an extended library Zip class
        $library = $this->libraries->get('Zip', null, array('tests/libraries/testLoadExtendedLibrary'));
        $this->assertInstanceOf('Application\Library\MY_Zip', $library);

        // Test if it's also an instance of the parent class
        $this->assertInstanceOf('FuzeWorks\Library\FW_Zip', $library);
    }

    /**
     * @depends testLoadBasicLibrary
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testFailLoadLibrary()
    {
        $library = $this->libraries->get('FailLoadLibrary');
    }

    /**
     * @depends testLoadExtendedLibrary
     */
    public function testDifferentPrefix()
    {
        // Test if the prefix can be changed
        Factory::getInstance()->config->getConfig('main')->application_prefix = 'unit_test_';

        // Let's extend the Encryption class
        $library = $this->libraries->get('Encryption', null, array('tests/libraries/testDifferentPrefix'));

        // Test if it has both instances
        $this->assertInstanceOf('FuzeWorks\Library\FW_Encryption', $library);
        $this->assertInstanceOf('Application\Library\unit_test_Encryption', $library);
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testAddLibraryPathFail()
    {
        // First test if the library is not loaded yet
        $this->assertFalse(class_exists('TestAddLibraryPath', false));

        // Now test if the library can be loaded (hint: it can not)
        $this->libraries->get('TestAddLibraryPath');
    }

    /**
     * @depends testAddLibraryPathFail
     */
    public function testAddLibraryPath()
    {
        // Add the libraryPath
        $this->libraries->addLibraryPath('tests/libraries/testAddLibraryPath');

        // And try to load it again
        $this->assertInstanceOf('Application\Library\TestAddLibraryPath', $this->libraries->get('TestAddLibraryPath'));
    }

    public function testRemoveLibraryPath()
    {
        // Test if the path does NOT exist
        $this->assertFalse(in_array('tests/libraries/testRemoveLibraryPath', $this->libraries->getLibraryPaths()));

        // Add it
        $this->libraries->addLibraryPath('tests/libraries/testRemoveLibraryPath');

        // Assert if it's there
        $this->assertTrue(in_array('tests/libraries/testRemoveLibraryPath', $this->libraries->getLibraryPaths()));

        // Remove it
        $this->libraries->removeLibraryPath('tests/libraries/testRemoveLibraryPath');

        // And test if it's gone again
        $this->assertFalse(in_array('tests/libraries/testRemoveLibraryPath', $this->libraries->getLibraryPaths()));
    }

    public function tearDown()
    {
        Factory::getInstance()->config->getConfig('main')->revert();
    }

}
