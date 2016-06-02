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
 * Class Utf8Test.
 *
 * Core testing suite, will test UTF8 class functionality
 */
class utf8Test extends CoreTestAbstract
{

    protected $factory;

    public function setUp()
    {
        $this->factory = Factory::getInstance();

        $this->factory->config->getConfig('main')->charset = 'UTF-8';
        $this->utf8 = new Mock_Core_Utf8();
    }

    // --------------------------------------------------------------------

    /**
     * __construct() test
     *
     * @covers  Utf8::__construct
     */
    public function test___construct()
    {
        if (defined('PREG_BAD_UTF8_ERROR') && (ICONV_ENABLED === TRUE OR MB_ENABLED === TRUE) && strtoupper($this->factory->config->getConfig('main')->charset) === 'UTF-8')
        {
            $this->assertTrue(UTF8_ENABLED);
        }
        else
        {
            $this->assertFalse(UTF8_ENABLED);
        }
    }

    // --------------------------------------------------------------------

    /**
     * is_ascii() test
     *
     * Note: DO NOT move this below test_clean_string()
     */
    public function test_is_ascii()
    {
        $this->assertTrue($this->utf8->is_ascii('foo bar'));
        $this->assertFalse($this->utf8->is_ascii('тест'));
    }

    // --------------------------------------------------------------------

    /**
     * clean_string() test
     *
     * @depends test_is_ascii
     * @covers  Utf8::clean_string
     */
    public function test_clean_string()
    {
        $this->assertEquals('foo bar', $this->utf8->clean_string('foo bar'));

        $illegal_utf8 = "\xc0тест";
        if (MB_ENABLED)
        {
            $this->assertEquals('тест', $this->utf8->clean_string($illegal_utf8));
        }
        elseif (ICONV_ENABLED)
        {
            // This is a known issue, iconv doesn't always work with //IGNORE
            $this->assertTrue(in_array($this->utf8->clean_string($illegal_utf8), array('тест', ''), TRUE));
        }
        else
        {
            $this->assertEquals($illegal_utf8, $this->utf8->clean_string($illegal_utf8));
        }
    }

    // --------------------------------------------------------------------

    /**
     * convert_to_utf8() test
     *
     * @covers  Utf8::convert_to_utf8
     */
    public function test_convert_to_utf8()
    {
        $this->markTestSkipped("Does not work properly yet. See issue #95");
        if (MB_ENABLED OR ICONV_ENABLED)
        {
            $this->assertEquals('тест', $this->utf8->convert_to_utf8('����', 'WINDOWS-1251'));
        }
        else
        {
            $this->assertFalse($this->utf8->convert_to_utf8('����', 'WINDOWS-1251'));
        }
    }
}
