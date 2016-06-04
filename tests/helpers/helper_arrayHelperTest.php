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
 * Class ArrayHelperTest.
 *
 * Helpers testing suite, will test specific helper
 */
class arrayHelperTest extends CoreTestAbstract
{

	public $my_array = array(
		'foo'    => 'bar',
		'sally'  => 'jim',
		'maggie' => 'bessie',
		'herb'   => 'cook'
	);

    public function setUp()
    {
        // Load Helper
        Factory::getInstance()->helpers->load('array');
    }

	// ------------------------------------------------------------------------

	public function test_element_with_existing_item()
	{
		$this->assertEquals(FALSE, element('testing', $this->my_array));
		$this->assertEquals('not set', element('testing', $this->my_array, 'not set'));
		$this->assertEquals('bar', element('foo', $this->my_array));
	}

	// ------------------------------------------------------------------------

	public function test_random_element()
	{
		// Send a string, not an array to random_element
		$this->assertEquals('my string', random_element('my string'));

		// Test sending an array
		$this->assertEquals(TRUE, in_array(random_element($this->my_array), $this->my_array));
	}

	// ------------------------------------------------------------------------

	public function test_elements()
	{
		$this->assertEquals(TRUE, is_array(elements('test', $this->my_array)));
		$this->assertEquals(TRUE, is_array(elements('foo', $this->my_array)));
	}
}
