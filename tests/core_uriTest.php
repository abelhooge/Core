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
 * Class URITest.
 *
 * Core testing suite, will test URI class functionality
 */
class uriTest extends CoreTestAbstract {

	public function setUp()
	{
		$this->uri = new Mock_Core_URI();
	}

	// --------------------------------------------------------------------

	public function test_filter_uri_passing()
	{
		$this->uri->_set_permitted_uri_chars('a-z 0-9~%.:_\-');

		$str = 'abc01239~%.:_-';
		$this->uri->filter_uri($str);
	}

	// --------------------------------------------------------------------

	/**
	 * @expectedException FuzeWorks\Exception\UriException
	 */
	public function test_filter_uri_throws_error()
	{
		$this->uri->config->routing->enable_query_strings = false;
		$this->uri->_set_permitted_uri_chars('a-z 0-9~%.:_\-');
		$segment = '$this()'; // filter_uri() accepts by reference
		$this->uri->filter_uri($segment);
	}

	// --------------------------------------------------------------------

	public function test_segment()
	{
		$this->uri->segments = array(1 => 'controller');
		$this->assertEquals($this->uri->segment(1), 'controller');
		$this->assertEquals($this->uri->segment(2, 'default'), 'default');
	}

	// --------------------------------------------------------------------

	public function test_rsegment()
	{
		$this->uri->rsegments = array(1 => 'method');
		$this->assertEquals($this->uri->rsegment(1), 'method');
		$this->assertEquals($this->uri->rsegment(2, 'default'), 'default');
	}

	// --------------------------------------------------------------------

	public function test_uri_to_assoc()
	{
		$this->uri->segments = array('a', '1', 'b', '2', 'c', '3');

		$this->assertEquals(
			array('a' => '1', 'b' => '2', 'c' => '3'),
			$this->uri->uri_to_assoc(1)
		);

		$this->assertEquals(
			array('b' => '2', 'c' => '3'),
			$this->uri->uri_to_assoc(3)
		);

		$this->uri->keyval = array(); // reset cache
		$this->uri->segments = array('a', '1', 'b', '2', 'c');

		$this->assertEquals(
			array('a' => '1', 'b' => '2', 'c' => FALSE),
			$this->uri->uri_to_assoc(1)
		);

		$this->uri->keyval = array(); // reset cache
		$this->uri->segments = array('a', '1');

		// test default
		$this->assertEquals(
			array('a' => '1', 'b' => FALSE),
			$this->uri->uri_to_assoc(1, array('a', 'b'))
		);
	}

	// --------------------------------------------------------------------

	public function test_ruri_to_assoc()
	{
		$this->uri->rsegments = array('x', '1', 'y', '2', 'z', '3');

		$this->assertEquals(
			array('x' => '1', 'y' => '2', 'z' => '3'),
			$this->uri->ruri_to_assoc(1)
		);

		$this->assertEquals(
			array('y' => '2', 'z' => '3'),
			$this->uri->ruri_to_assoc(3)
		);

		$this->uri->keyval = array(); // reset cache
		$this->uri->rsegments = array('x', '1', 'y', '2', 'z');

		$this->assertEquals(
			array('x' => '1', 'y' => '2', 'z' => FALSE),
			$this->uri->ruri_to_assoc(1)
		);

		$this->uri->keyval = array(); // reset cache
		$this->uri->rsegments = array('x', '1');

		// test default
		$this->assertEquals(
			array('x' => '1', 'y' => FALSE),
			$this->uri->ruri_to_assoc(1, array('x', 'y'))
		);
	}

	// --------------------------------------------------------------------

	public function test_assoc_to_uri()
	{
		//$this->uri->config->set_item('uri_string_slashes', 'none');
		$this->assertEquals('a/1/b/2', $this->uri->assoc_to_uri(array('a' => '1', 'b' => '2')));
	}

	// --------------------------------------------------------------------

	public function test_slash_segment()
	{
		$this->uri->segments[1] = 'segment';
		$this->uri->rsegments[1] = 'segment';

		$this->assertEquals('/segment/', $this->uri->slash_segment(1, 'both'));
		$this->assertEquals('/segment/', $this->uri->slash_rsegment(1, 'both'));

		$a = '/segment';
		$this->assertEquals('/segment', $this->uri->slash_segment(1, 'leading'));
		$this->assertEquals('/segment', $this->uri->slash_rsegment(1, 'leading'));

		$this->assertEquals('segment/', $this->uri->slash_segment(1, 'trailing'));
		$this->assertEquals('segment/', $this->uri->slash_rsegment(1, 'trailing'));
	}

}
