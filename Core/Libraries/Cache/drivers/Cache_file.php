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

namespace FuzeWorks\Library;
use FuzeWorks\Helpers;
use FuzeWorks\Config;

/**
 * FuzeWorks File Caching Class
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @subpackage	Libraries
 * @category	Core
 * @author		EllisLab Dev Team
 * @link
 * @license	http://opensource.org/licenses/MIT	MIT License
 */
class FW_Cache_file extends FW_Driver {

	/**
	 * Directory in which to save cache files
	 *
	 * @var string
	 */
	protected $_cache_path;

	/**
	 * Initialize file-based cache
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// Load the required helpers
		Helpers::load('file');
		Helpers::load('common');

		$path = Config::get('cache')->cache_path;
		$this->_cache_path = ($path === '') ? 'Application'.DS.'Cache/' : $path;
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch from cache
	 *
	 * @param	string	$id	Cache ID
	 * @return	mixed	Data on success, FALSE on failure
	 */
	public function get($id)
	{
		$data = $this->_get($id);
		return is_array($data) ? $data['data'] : FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Save into cache
	 *
	 * @param	string	$id	Cache ID
	 * @param	mixed	$data	Data to store
	 * @param	int	$ttl	Time to live in seconds
	 * @param	bool	$raw	Whether to store the raw value (unused)
	 * @return	bool	TRUE on success, FALSE on failure
	 */
	public function save($id, $data, $ttl = 60, $raw = FALSE)
	{
		$contents = array(
			'time'		=> time(),
			'ttl'		=> $ttl,
			'data'		=> $data
		);

		if (write_file($this->_cache_path.$id, serialize($contents)))
		{
			chmod($this->_cache_path.$id, 0640);
			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param	mixed	unique identifier of item in cache
	 * @return	bool	true on success/false on failure
	 */
	public function delete($id)
	{
		return file_exists($this->_cache_path.$id) ? unlink($this->_cache_path.$id) : FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Increment a raw value
	 *
	 * @param	string	$id	Cache ID
	 * @param	int	$offset	Step/value to add
	 * @return	New value on success, FALSE on failure
	 */
	public function increment($id, $offset = 1)
	{
		$data = $this->_get($id);

		if ($data === FALSE)
		{
			$data = array('data' => 0, 'ttl' => 60);
		}
		elseif ( ! is_int($data['data']))
		{
			return FALSE;
		}

		$new_value = $data['data'] + $offset;
		return $this->save($id, $new_value, $data['ttl'])
			? $new_value
			: FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Decrement a raw value
	 *
	 * @param	string	$id	Cache ID
	 * @param	int	$offset	Step/value to reduce by
	 * @return	New value on success, FALSE on failure
	 */
	public function decrement($id, $offset = 1)
	{
		$data = $this->_get($id);

		if ($data === FALSE)
		{
			$data = array('data' => 0, 'ttl' => 60);
		}
		elseif ( ! is_int($data['data']))
		{
			return FALSE;
		}

		$new_value = $data['data'] - $offset;
		return $this->save($id, $new_value, $data['ttl'])
			? $new_value
			: FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the Cache
	 *
	 * @return	bool	false on failure/true on success
	 */
	public function clean()
	{
		return delete_files($this->_cache_path, FALSE, TRUE);
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * Not supported by file-based caching
	 *
	 * @param	string	user/filehits
	 * @return	mixed	FALSE
	 */
	public function cache_info($type = NULL)
	{
		return get_dir_file_info($this->_cache_path);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param	mixed	key to get cache metadata on
	 * @return	mixed	FALSE on failure, array on success.
	 */
	public function get_metadata($id)
	{
		if ( ! file_exists($this->_cache_path.$id))
		{
			return FALSE;
		}

		$data = unserialize(file_get_contents($this->_cache_path.$id));

		if (is_array($data))
		{
			$mtime = filemtime($this->_cache_path.$id);

			if ( ! isset($data['ttl']))
			{
				return FALSE;
			}

			return array(
				'expire' => $mtime + $data['ttl'],
				'mtime'	 => $mtime
			);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is supported
	 *
	 * In the file driver, check to see that the cache directory is indeed writable
	 *
	 * @return	bool
	 */
	public function is_supported()
	{
		return is_really_writable($this->_cache_path);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get all data
	 *
	 * Internal method to get all the relevant data about a cache item
	 *
	 * @param	string	$id	Cache ID
	 * @return	mixed	Data array on success or FALSE on failure
	 */
	protected function _get($id)
	{
		if ( ! is_file($this->_cache_path.$id))
		{
			return FALSE;
		}

		$data = unserialize(file_get_contents($this->_cache_path.$id));

		if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
		{
			unlink($this->_cache_path.$id);
			return FALSE;
		}

		return $data;
	}

}
