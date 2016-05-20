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

use FuzeWorks\Logger;
use FuzeWorks\Helpers;
use FuzeWorks\Libraries;
use FuzeWorks\DatabaseException;

/**
 * Database Utility Class
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/database/
 * @license		http://opensource.org/licenses/MIT	MIT License
 */
abstract class FW_DB_utility {

	/**
	 * Database object
	 *
	 * @var	object
	 */
	protected $db;

	// --------------------------------------------------------------------

	/**
	 * List databases statement
	 *
	 * @var	string
	 */
	protected $_list_databases		= FALSE;

	/**
	 * OPTIMIZE TABLE statement
	 *
	 * @var	string
	 */
	protected $_optimize_table	= FALSE;

	/**
	 * REPAIR TABLE statement
	 *
	 * @var	string
	 */
	protected $_repair_table	= FALSE;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	object	&$db	Database object
	 * @return	void
	 */
	public function __construct(&$db)
	{
		$this->db =& $db;
		Logger::log('Database Utility Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * @return	array
	 */
	public function list_databases()
	{
		// Is there a cached result?
		if (isset($this->db->data_cache['db_names']))
		{
			return $this->db->data_cache['db_names'];
		}
		elseif ($this->_list_databases === FALSE)
		{
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}

		$this->db->data_cache['db_names'] = array();

		$query = $this->db->query($this->_list_databases);
		if ($query === FALSE)
		{
			return $this->db->data_cache['db_names'];
		}

		for ($i = 0, $query = $query->result_array(), $c = count($query); $i < $c; $i++)
		{
			$this->db->data_cache['db_names'][] = current($query[$i]);
		}

		return $this->db->data_cache['db_names'];
	}

	// --------------------------------------------------------------------

	/**
	 * Determine if a particular database exists
	 *
	 * @param	string	$database_name
	 * @return	bool
	 */
	public function database_exists($database_name)
	{
		return in_array($database_name, $this->list_databases());
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @param	string	$table_name
	 * @return	mixed
	 */
	public function optimize_table($table_name)
	{
		if ($this->_optimize_table === FALSE)
		{
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}

		$query = $this->db->query(sprintf($this->_optimize_table, $this->db->escape_identifiers($table_name)));
		if ($query !== FALSE)
		{
			$query = $query->result_array();
			return current($query);
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Database
	 *
	 * @return	mixed
	 */
	public function optimize_database()
	{
		if ($this->_optimize_table === FALSE)
		{
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}

		$result = array();
		foreach ($this->db->list_tables() as $table_name)
		{
			$res = $this->db->query(sprintf($this->_optimize_table, $this->db->escape_identifiers($table_name)));
			if (is_bool($res))
			{
				return $res;
			}

			// Build the result array...
			$res = $res->result_array();
			$res = current($res);
			$key = str_replace($this->db->database.'.', '', current($res));
			$keys = array_keys($res);
			unset($res[$keys[0]]);

			$result[$key] = $res;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Repair Table
	 *
	 * @param	string	$table_name
	 * @return	mixed
	 */
	public function repair_table($table_name)
	{
		if ($this->_repair_table === FALSE)
		{
			return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
		}

		$query = $this->db->query(sprintf($this->_repair_table, $this->db->escape_identifiers($table_name)));
		if (is_bool($query))
		{
			return $query;
		}

		$query = $query->result_array();
		return current($query);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate CSV from a query result object
	 *
	 * @param	object	$query		Query result object
	 * @param	string	$delim		Delimiter (default: ,)
	 * @param	string	$newline	Newline character (default: \n)
	 * @param	string	$enclosure	Enclosure (default: ")
	 * @return	string
	 */
	public function csv_from_result($query, $delim = ',', $newline = "\n", $enclosure = '"')
	{
		if ( ! is_object($query) OR ! method_exists($query, 'list_fields'))
		{
			throw new DatabaseException('You must submit a valid result object', 1);
		}

		$out = '';
		// First generate the headings from the table column names
		foreach ($query->list_fields() as $name)
		{
			$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $name).$enclosure.$delim;
		}

		$out = substr($out, 0, -strlen($delim)).$newline;

		// Next blast through the result array and build out the rows
		while ($row = $query->unbuffered_row('array'))
		{
			$line = array();
			foreach ($row as $item)
			{
				$line[] = $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure;
			}
			$out .= implode($delim, $line).$newline;
		}

		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate XML data from a query result object
	 *
	 * @param	object	$query	Query result object
	 * @param	array	$params	Any preferences
	 * @return	string
	 */
	public function xml_from_result($query, $params = array())
	{
		if ( ! is_object($query) OR ! method_exists($query, 'list_fields'))
		{
			throw new DatabaseException('You must submit a valid result object', 1);
		}

		// Set our default values
		foreach (array('root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t") as $key => $val)
		{
			if ( ! isset($params[$key]))
			{
				$params[$key] = $val;
			}
		}

		// Create variables for convenience
		extract($params);

		// Load the xml helper
		Helpers::load('xml');

		// Generate the result
		$xml = '<'.$root.'>'.$newline;
		while ($row = $query->unbuffered_row())
		{
			$xml .= $tab.'<'.$element.'>'.$newline;
			foreach ($row as $key => $val)
			{
				$xml .= $tab.$tab.'<'.$key.'>'.xml_convert($val).'</'.$key.'>'.$newline;
			}
			$xml .= $tab.'</'.$element.'>'.$newline;
		}

		return $xml.'</'.$root.'>'.$newline;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Backup
	 *
	 * @param	array	$params
	 * @return	string
	 */
	public function backup($params = array())
	{
		// If the parameters have not been submitted as an
		// array then we know that it is simply the table
		// name, which is a valid short cut.
		if (is_string($params))
		{
			$params = array('tables' => $params);
		}

		// Set up our default preferences
		$prefs = array(
			'tables'		=> array(),
			'ignore'		=> array(),
			'filename'		=> '',
			'format'		=> 'gzip', // gzip, zip, txt
			'add_drop'		=> TRUE,
			'add_insert'		=> TRUE,
			'newline'		=> "\n",
			'foreign_key_checks'	=> TRUE
		);

		// Did the user submit any preferences? If so set them....
		if (count($params) > 0)
		{
			foreach ($prefs as $key => $val)
			{
				if (isset($params[$key]))
				{
					$prefs[$key] = $params[$key];
				}
			}
		}

		// Are we backing up a complete database or individual tables?
		// If no table names were submitted we'll fetch the entire table list
		if (count($prefs['tables']) === 0)
		{
			$prefs['tables'] = $this->db->list_tables();
		}

		// Validate the format
		if ( ! in_array($prefs['format'], array('gzip', 'zip', 'txt'), TRUE))
		{
			$prefs['format'] = 'txt';
		}

		// Is the encoder supported? If not, we'll either issue an
		// error or use plain text depending on the debug settings
		if (($prefs['format'] === 'gzip' && ! function_exists('gzencode'))
			OR ($prefs['format'] === 'zip' && ! function_exists('gzcompress')))
		{
			if ($this->db->db_debug)
			{
				return $this->db->display_error('db_unsupported_compression');
			}

			$prefs['format'] = 'txt';
		}

		// Was a Zip file requested?
		if ($prefs['format'] === 'zip')
		{
			// Set the filename if not provided (only needed with Zip files)
			if ($prefs['filename'] === '')
			{
				$prefs['filename'] = (count($prefs['tables']) === 1 ? $prefs['tables'] : $this->db->database)
							.date('Y-m-d_H-i', time()).'.sql';
			}
			else
			{
				// If they included the .zip file extension we'll remove it
				if (preg_match('|.+?\.zip$|', $prefs['filename']))
				{
					$prefs['filename'] = str_replace('.zip', '', $prefs['filename']);
				}

				// Tack on the ".sql" file extension if needed
				if ( ! preg_match('|.+?\.sql$|', $prefs['filename']))
				{
					$prefs['filename'] .= '.sql';
				}
			}

			// Load the Zip class and output it
			$zip = Libraries::get('zip');
			$zip->add_data($prefs['filename'], $this->_backup($prefs));
			return $zip->get_zip();
		}
		elseif ($prefs['format'] === 'txt') // Was a text file requested?
		{
			return $this->_backup($prefs);
		}
		elseif ($prefs['format'] === 'gzip') // Was a Gzip file requested?
		{
			return gzencode($this->_backup($prefs));
		}

		return;
	}

}
