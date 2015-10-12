<?php
/**
 * FuzeWorks
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
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */

use \FuzeWorks\Core;
use \FuzeWorks\Layout;
use \FuzeWorks\Events;
use \FuzeWorks\TemplateEngine\TemplateEngine;

/**
 * Class RouterTest
 *
 * This test will test the router
 */
class LayoutTest extends CoreTestAbstract
{

    public function testGetFileExtensions() {
        // Test getting php files
        $this->assertEquals('php', Layout::getExtensionFromFile('class.test.php'));
        $this->assertEquals('php', Layout::getExtensionFromFile('class.test.org.php'));
    }

    /**
     * @depends testGetFileExtensions
     */
    public function testGetFilePath(){

        // Extensions to be used in this test
        $extensions = array('php', 'json');

        // Basic path
        Layout::setFileFromString('test', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/view.test.php', Layout::getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', Layout::getDirectory());

        // Alternate file extension
        Layout::setFileFromString('JSON', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/view.JSON.json', Layout::getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', Layout::getDirectory());

        // Complex deeper path
        Layout::setFileFromString('Deeper/test', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/Deeper/view.test.php', Layout::getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', Layout::getDirectory());
    }

    /**
     * @expectedException \FuzeWorks\LayoutException
     */
    public function testMissingDirectory() {
        // Directory that does not exist
        Layout::setFileFromString('test', 'tests/layout/doesNotExist/', array('php'));
    }

    /**
     * @expectedException \FuzeWorks\LayoutException
     */
    public function testMissingFile() {
        Layout::setFileFromString('test', 'tests/layout/testMissingFile/', array('php'));
    }

    /**
     * @expectedException \FuzeWorks\LayoutException
     */
    public function testUnknownFileExtension() {
        Layout::setFileFromString('test', 'tests/layout/testUnknownFileExtension/', array('php'));
    }

    public function testGetEngineFromExtension() {
        Layout::loadTemplateEngines();

        // Test all the default engines
        $this->assertInstanceOf('\FuzeWorks\TemplateEngine\PHPEngine', Layout::getEngineFromExtension('php'));
        $this->assertInstanceOf('\FuzeWorks\TemplateEngine\JSONEngine', Layout::getEngineFromExtension('json'));
        $this->assertInstanceOf('\FuzeWorks\TemplateEngine\SmartyEngine', Layout::getEngineFromExtension('tpl'));
    }

    /**
     * @depends testGetEngineFromExtension
     * @expectedException \FuzeWorks\LayoutException
     */
    public function testGetEngineFromExtensionFail() {
        Layout::getEngineFromExtension('faulty');
    }

    /**
     * @depends testGetEngineFromExtension
     */
    public function testCustomEngine() {

        // Create the engine
        $mock = $this->getMockBuilder('\FuzeWorks\TemplateEngine\TemplateEngine')->getMock();

        // Add the methods
        $mock->method('get')->willReturn('output');

        // And listen for usage
        $mock->expects($this->once())->method('get')->with('tests/layout/testCustomEngine/view.test.test');

        // Register the engine
        Layout::registerEngine($mock, 'Custom', array('test'));

        // And run the engine
        $this->assertEquals('output', Layout::get('test', 'tests/layout/testCustomEngine/'));
    }

    public function testPHPEngine() {

        // Directory of these tests
        $directory = 'tests/layout/testEngines/';

        $this->assertEquals('PHP Template Check', Layout::get('php', $directory));
    }

    public function testJSONEngine() {

        // Directory of these tests
        $directory = 'tests/layout/testEngines/';

        $this->assertEquals('JSON Template Check', json_decode(Layout::get('json', $directory), true)[0]);
    }
}