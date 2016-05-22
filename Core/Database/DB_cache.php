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

use FuzeWorks\Helpers;
use FuzeWorks\Logger;

/**
 * Database Cache Class
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/database/
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @todo		Fix URI
 */
class FW_DB_Cache {

	/**
	 * CI Singleton
	 *
	 * @var	object
	 */
	public $CI;

	/**
	 * Database object
	 *
	 * Allows passing of DB object so that multiple database connections
	 * and returned DB objects can be supported.
	 *
	 * @var	object
	 */
	public $db;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param	object	&$db
	 * @return	void
	 */
	public function __construct(&$db)
	{
		// Assign the main CI object to $this->CI and load the file helper since we use it a lot
		Helpers::load('file');

		$this->check_path();
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache Directory Path
	 *
	 * @param	string	$path	Path to the cache directory
	 * @return	bool
	 */
	public function check_path($path = '')
	{
		if ($path === '')
		{
			if ($this->db->cachedir === '')
			{
				return $this->db->cache_off();
			}

			$path = $this->db->cachedir;
		}

		// Add a trailing slash to the path if needed
		$path = realpath($path)
			? rtrim(realpath($path), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
			: rtrim($path, '/').'/';

		if ( ! is_dir($path))
		{
			Logger::logDebug('DB cache path error: '.$path);

			// If the path is wrong we'll turn off caching
			return $this->db->cache_off();
		}

		if ( ! is_really_writable($path))
		{
			Logger::logDebug('DB cache dir not writable: '.$path);

			// If the path is not really writable we'll turn off caching
			return $this->db->cache_off();
		}

		$this->db->cachedir = $path;
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve a cached query
	 *
	 * The URI being requested will become the name of the cache sub-folder.
	 * An MD5 hash of the SQL statement will become the cache file name.
	 *
	 * @param	string	$sql
	 * @return	string
	 */
	public function read($sql)
	{
		$segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
		$filepath = $this->db->cachedir.$segment_one.'+'.$segment_two.'/'.md5($sql);

		if (FALSE === ($cachedata = @file_get_contents($filepath)))
		{
			return FALSE;
		}

		return unserialize($cachedata);
	}

	// --------------------------------------------------------------------

	/**
	 * Write a query to a cache file
	 *
	 * @param	string	$sql
	 * @param	object	$object
	 * @return	bool
	 */
	public function write($sql, $object)
	{
		$segment_one = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
		$dir_path = $this->db->cachedir.$segment_one.'+'.$segment_two.'/';
		$filename = md5($sql);

		if ( ! is_dir($dir_path) && ! @mkdir($dir_path, 0750))
		{
			return FALSE;
		}

		if (write_file($dir_path.$filename, serialize($object)) === FALSE)
		{
			return FALSE;
		}

		chmod($dir_path.$filename, 0640);
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache files within a particular directory
	 *
	 * @param	string	$segment_one
	 * @param	string	$segment_two
	 * @return	void
	 */
	public function delete($segment_one = '', $segment_two = '')
	{
		if ($segment_one === '')
		{
			$segment_one  = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
		}

		if ($segment_two === '')
		{
			$segment_two = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
		}

		$dir_path = $this->db->cachedir.$segment_one.'+'.$segment_two.'/';
		delete_files($dir_path, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete all existing cache files
	 *
	 * @return	void
	 */
	public function delete_all()
	{
		delete_files($this->db->cachedir, TRUE, TRUE);
	}

}
