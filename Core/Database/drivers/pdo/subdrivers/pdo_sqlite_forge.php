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
 * PDO SQLite Forge Class
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/database/
 * @license		http://opensource.org/licenses/MIT	MIT License
 */
class FW_DB_pdo_sqlite_forge extends FW_DB_pdo_forge {

	/**
	 * CREATE TABLE IF statement
	 *
	 * @var	string
	 */
	protected $_create_table_if	= 'CREATE TABLE IF NOT EXISTS';

	/**
	 * DROP TABLE IF statement
	 *
	 * @var	string
	 */
	protected $_drop_table_if	= 'DROP TABLE IF EXISTS';

	/**
	 * UNSIGNED support
	 *
	 * @var	bool|array
	 */
	protected $_unsigned		= FALSE;

	/**
	 * NULL value representation in CREATE/ALTER TABLE statements
	 *
	 * @var	string
	 */
	protected $_null		= 'NULL';

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	object	&$db	Database object
	 * @return	void
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);

		if (version_compare($this->db->version(), '3.3', '<'))
		{
			$this->_create_table_if = FALSE;
			$this->_drop_table_if   = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Create database
	 *
	 * @param	string	$db_name	(ignored)
	 * @return	bool
	 */
	public function create_database($db_name = '')
	{
		// In SQLite, a database is created when you connect to the database.
		// We'll return TRUE so that an error isn't generated
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param	string	$db_name	(ignored)
	 * @return	bool
	 */
	public function drop_database($db_name = '')
	{
		// In SQLite, a database is dropped when we delete a file
		if (file_exists($this->db->database))
		{
			// We need to close the pseudo-connection first
			$this->db->close();
			if ( ! @unlink($this->db->database))
			{
				return $this->db->db_debug ? $this->db->display_error('db_unable_to_drop') : FALSE;
			}
			elseif ( ! empty($this->db->data_cache['db_names']))
			{
				$key = array_search(strtolower($this->db->database), array_map('strtolower', $this->db->data_cache['db_names']), TRUE);
				if ($key !== FALSE)
				{
					unset($this->db->data_cache['db_names'][$key]);
				}
			}

			return TRUE;
		}

		return $this->db->db_debug ? $this->db->display_error('db_unable_to_drop') : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param	string	$alter_type	ALTER type
	 * @param	string	$table		Table name
	 * @param	mixed	$field		Column definition
	 * @return	string|string[]
	 */
	protected function _alter_table($alter_type, $table, $field)
	{
		if ($alter_type === 'DROP' OR $alter_type === 'CHANGE')
		{
			// drop_column():
			//	BEGIN TRANSACTION;
			//	CREATE TEMPORARY TABLE t1_backup(a,b);
			//	INSERT INTO t1_backup SELECT a,b FROM t1;
			//	DROP TABLE t1;
			//	CREATE TABLE t1(a,b);
			//	INSERT INTO t1 SELECT a,b FROM t1_backup;
			//	DROP TABLE t1_backup;
			//	COMMIT;

			return FALSE;
		}

		return parent::_alter_table($alter_type, $table, $field);
	}

	// --------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param	array	$field
	 * @return	string
	 */
	protected function _process_column($field)
	{
		return $this->db->escape_identifiers($field['name'])
			.' '.$field['type']
			.$field['auto_increment']
			.$field['null']
			.$field['unique']
			.$field['default'];
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param	array	&$attributes
	 * @return	void
	 */
	protected function _attr_type(&$attributes)
	{
		switch (strtoupper($attributes['TYPE']))
		{
			case 'ENUM':
			case 'SET':
				$attributes['TYPE'] = 'TEXT';
				return;
			default: return;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute AUTO_INCREMENT
	 *
	 * @param	array	&$attributes
	 * @param	array	&$field
	 * @return	void
	 */
	protected function _attr_auto_increment(&$attributes, &$field)
	{
		if ( ! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === TRUE && stripos($field['type'], 'int') !== FALSE)
		{
			$field['type'] = 'INTEGER PRIMARY KEY';
			$field['default'] = '';
			$field['null'] = '';
			$field['unique'] = '';
			$field['auto_increment'] = ' AUTOINCREMENT';

			$this->primary_keys = array();
		}
	}

}
