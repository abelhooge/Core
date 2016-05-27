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
 * Class CommonHelperTest.
 *
 * Helpers testing suite, will test specific helper
 */
class commonHelperTest extends CoreTestAbstract
{
    public function setUp()
    {
        // Load Helper
        Factory::getInstance()->getHelpers()->load('common');
    }

	public function test_stringify_attributes()
	{
		$this->assertEquals(' class="foo" id="bar"', _stringify_attributes(array('class' => 'foo', 'id' => 'bar')));

		$atts = new stdClass;
		$atts->class = 'foo';
		$atts->id = 'bar';
		$this->assertEquals(' class="foo" id="bar"', _stringify_attributes($atts));

		$atts = new stdClass;
		$this->assertEquals('', _stringify_attributes($atts));

		$this->assertEquals(' class="foo" id="bar"', _stringify_attributes('class="foo" id="bar"'));

		$this->assertEquals('', _stringify_attributes(array()));
	}

	// ------------------------------------------------------------------------

	public function test_stringify_js_attributes()
	{
		$this->assertEquals('width=800,height=600', _stringify_attributes(array('width' => '800', 'height' => '600'), TRUE));

		$atts = new stdClass;
		$atts->width = 800;
		$atts->height = 600;
		$this->assertEquals('width=800,height=600', _stringify_attributes($atts, TRUE));
	}	

	// ------------------------------------------------------------------------

	public function test_html_escape()
	{
		$this->assertEquals(
			html_escape('Here is a string containing "quoted" text.'),
			'Here is a string containing &quot;quoted&quot; text.'
		);

		$this->assertEquals(
			html_escape(array('associative' => 'and', array('multi' => 'dimentional'))),
			array('associative' => 'and', array('multi' => 'dimentional'))
		);
	}

}
