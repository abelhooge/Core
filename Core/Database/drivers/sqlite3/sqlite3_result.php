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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

/**
 * SQLite3 Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @category	Database
 * @author		Andrey Andreev
 * @link		https://codeigniter.com/user_guide/database/
 * @license		http://opensource.org/licenses/MIT	MIT License
 */
class CI_DB_sqlite3_result extends CI_DB_result {

	/**
	 * Number of fields in the result set
	 *
	 * @return	int
	 */
	public function num_fields()
	{
		return $this->result_id->numColumns();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Field Names
	 *
	 * Generates an array of column names
	 *
	 * @return	array
	 */
	public function list_fields()
	{
		$field_names = array();
		for ($i = 0, $c = $this->num_fields(); $i < $c; $i++)
		{
			$field_names[] = $this->result_id->columnName($i);
		}

		return $field_names;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data
	 *
	 * Generates an array of objects containing field meta-data
	 *
	 * @return	array
	 */
	public function field_data()
	{
		static $data_types = array(
			SQLITE3_INTEGER	=> 'integer',
			SQLITE3_FLOAT	=> 'float',
			SQLITE3_TEXT	=> 'text',
			SQLITE3_BLOB	=> 'blob',
			SQLITE3_NULL	=> 'null'
		);

		$retval = array();
		for ($i = 0, $c = $this->num_fields(); $i < $c; $i++)
		{
			$retval[$i]			= new stdClass();
			$retval[$i]->name		= $this->result_id->columnName($i);

			$type = $this->result_id->columnType($i);
			$retval[$i]->type		= isset($data_types[$type]) ? $data_types[$type] : $type;

			$retval[$i]->max_length		= NULL;
		}

		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Free the result
	 *
	 * @return	void
	 */
	public function free_result()
	{
		if (is_object($this->result_id))
		{
			$this->result_id->finalize();
			$this->result_id = NULL;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Returns the result set as an array
	 *
	 * @return	array
	 */
	protected function _fetch_assoc()
	{
		return $this->result_id->fetchArray(SQLITE3_ASSOC);
	}

	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Returns the result set as an object
	 *
	 * @param	string	$class_name
	 * @return	object
	 */
	protected function _fetch_object($class_name = 'stdClass')
	{
		// No native support for fetching rows as objects
		if (($row = $this->result_id->fetchArray(SQLITE3_ASSOC)) === FALSE)
		{
			return FALSE;
		}
		elseif ($class_name === 'stdClass')
		{
			return (object) $row;
		}

		$class_name = new $class_name();
		foreach (array_keys($row) as $key)
		{
			$class_name->$key = $row[$key];
		}

		return $class_name;
	}

	// --------------------------------------------------------------------

	/**
	 * Data Seek
	 *
	 * Moves the internal pointer to the desired offset. We call
	 * this internally before fetching results to make sure the
	 * result set starts at zero.
	 *
	 * @param	int	$n	(ignored)
	 * @return	array
	 */
	public function data_seek($n = 0)
	{
		// Only resetting to the start of the result set is supported
		return ($n > 0) ? FALSE : $this->result_id->reset();
	}

}
