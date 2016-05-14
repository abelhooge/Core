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
use FuzeWorks\Logger;

/**
 * FuzeWorks APC Caching Class
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
class FW_Cache_apc extends FW_Driver {

	/**
	 * Class constructor
	 *
	 * Only present so that an error message is logged
	 * if APC is not available.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		if ( ! $this->is_supported())
		{
			Logger::logError('Cache: Failed to initialize APC; extension not loaded/enabled?');
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Look for a value in the cache. If it exists, return the data
	 * if not, return FALSE
	 *
	 * @param	string
	 * @return	mixed	value that is stored/FALSE on failure
	 */
	public function get($id)
	{
		$success = FALSE;
		$data = apc_fetch($id, $success);

		if ($success === TRUE)
		{
			return is_array($data)
				? unserialize($data[0])
				: $data;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Save
	 *
	 * @param	string	$id	Cache ID
	 * @param	mixed	$data	Data to store
	 * @param	int	$ttol	Length of time (in seconds) to cache the data
	 * @param	bool	$raw	Whether to store the raw value
	 * @return	bool	TRUE on success, FALSE on failure
	 */
	public function save($id, $data, $ttl = 60, $raw = FALSE)
	{
		$ttl = (int) $ttl;

		return apc_store(
			$id,
			($raw === TRUE ? $data : array(serialize($data), time(), $ttl)),
			$ttl
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param	mixed	unique identifier of the item in the cache
	 * @return	bool	true on success/false on failure
	 */
	public function delete($id)
	{
		return apc_delete($id);
	}

	// ------------------------------------------------------------------------

	/**
	 * Increment a raw value
	 *
	 * @param	string	$id	Cache ID
	 * @param	int	$offset	Step/value to add
	 * @return	mixed	New value on success or FALSE on failure
	 */
	public function increment($id, $offset = 1)
	{
		return apc_inc($id, $offset);
	}

	// ------------------------------------------------------------------------

	/**
	 * Decrement a raw value
	 *
	 * @param	string	$id	Cache ID
	 * @param	int	$offset	Step/value to reduce by
	 * @return	mixed	New value on success or FALSE on failure
	 */
	public function decrement($id, $offset = 1)
	{
		return apc_dec($id, $offset);
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the cache
	 *
	 * @return	bool	false on failure/true on success
	 */
	public function clean()
	{
		return apc_clear_cache('user');
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * @param	string	user/filehits
	 * @return	mixed	array on success, false on failure
	 */
	 public function cache_info($type = NULL)
	 {
		 return apc_cache_info($type);
	 }

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param	mixed	key to get cache metadata on
	 * @return	mixed	array on success/false on failure
	 */
	public function get_metadata($id)
	{
		$success = FALSE;
		$stored = apc_fetch($id, $success);

		if ($success === FALSE OR count($stored) !== 3)
		{
			return FALSE;
		}

		list($data, $time, $ttl) = $stored;

		return array(
			'expire'	=> $time + $ttl,
			'mtime'		=> $time,
			'data'		=> unserialize($data)
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * is_supported()
	 *
	 * Check to see if APC is available on this system, bail if it isn't.
	 *
	 * @return	bool
	 */
	public function is_supported()
	{
		return (extension_loaded('apc') && ini_get('apc.enabled'));
	}
}
