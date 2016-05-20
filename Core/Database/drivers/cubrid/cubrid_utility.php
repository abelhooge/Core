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
 * CUBRID Utility Class
 *
 * Converted from CodeIgniter.
 *
 * @package		FuzeWorks
 * @category	Database
 * @author		Esen Sagynov
 * @link		https://codeigniter.com/user_guide/database/
 * @license		http://opensource.org/licenses/MIT	MIT License
 */
class CI_DB_cubrid_utility extends CI_DB_utility {

	/**
	 * List databases
	 *
	 * @return	array
	 */
	public function list_databases()
	{
		if (isset($this->db->data_cache['db_names']))
		{
			return $this->db->data_cache['db_names'];
		}

		return $this->db->data_cache['db_names'] = cubrid_list_dbs($this->db->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * CUBRID Export
	 *
	 * @param	array	Preferences
	 * @return	mixed
	 */
	protected function _backup($params = array())
	{
		// No SQL based support in CUBRID as of version 8.4.0. Database or
		// table backup can be performed using CUBRID Manager
		// database administration tool.
		return $this->db->display_error('db_unsupported_feature');
	}
}
