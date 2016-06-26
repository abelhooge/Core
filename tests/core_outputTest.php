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
 * Class OutputTest.
 *
 * Core testing suite, will test basic output class functionality
 */
class outputTest extends CoreTestAbstract {

	public $output;
	protected $_output_data = '';

	public function setUp()
	{
		$this->_output_data =<<<HTML
		<html>
			<head>
				<title>Basic HTML</title>
			</head>
			<body>
				Test
			</body>
		</html>
HTML;

		Factory::getInstance()->config->main->charset = 'UTF-8';
		$this->output = Factory::getInstance()->output;
	}

	// --------------------------------------------------------------------

	public function test_set_get_append_output()
	{
		$append = "<!-- comment /-->\n";

		$this->assertEquals(
			$this->_output_data.$append,
			$this->output
				->set_output($this->_output_data)
				->append_output("<!-- comment /-->\n")
				->get_output()
		);
	}

	// --------------------------------------------------------------------

	public function test_get_content_type()
	{
		$this->assertEquals('text/html', $this->output->get_content_type());
	}

	// --------------------------------------------------------------------

	public function test_get_header()
	{
		$this->assertNull($this->output->get_header('Non-Existent-Header'));

		// TODO: Find a way to test header() values as well. Currently,
		//	 PHPUnit prevents this by not using output buffering.

		$this->output->set_content_type('text/plain', 'WINDOWS-1251');
		$this->assertEquals(
			'text/plain; charset=WINDOWS-1251',
			$this->output->get_header('content-type')
		);
	}

}
