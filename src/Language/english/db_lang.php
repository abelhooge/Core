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

return array(

	'db_invalid_connection_str' => 'Unable to determine the database settings based on the connection string you submitted.',
	'db_unable_to_connect' => 'Unable to connect to your database server using the provided settings.',
	'db_unable_to_select' => 'Unable to select the specified database: %s',
	'db_unable_to_create' => 'Unable to create the specified database: %s',
	'db_invalid_query' => 'The query you submitted is not valid.',
	'db_must_set_table' => 'You must set the database table to be used with your query.',
	'db_must_use_set' => 'You must use the "set" method to update an entry.',
	'db_must_use_index' => 'You must specify an index to match on for batch updates.',
	'db_batch_missing_index' => 'One or more rows submitted for batch updating is missing the specified index.',
	'db_must_use_where' => 'Updates are not allowed unless they contain a "where" clause.',
	'db_del_must_use_where' => 'Deletes are not allowed unless they contain a "where" or "like" clause.',
	'db_field_param_missing' => 'To fetch fields requires the name of the table as a parameter.',
	'db_unsupported_function' => 'This feature is not available for the database you are using.',
	'db_transaction_failure' => 'Transaction failure: Rollback performed.',
	'db_unable_to_drop' => 'Unable to drop the specified database.',
	'db_unsupported_feature' => 'Unsupported feature of the database platform you are using.',
	'db_unsupported_compression' => 'The file compression format you chose is not supported by your server.',
	'db_filepath_error' => 'Unable to write data to the file path you have submitted.',
	'db_invalid_cache_path' => 'The cache path you submitted is not valid or writable.',
	'db_table_name_required' => 'A table name is required for that operation.',
	'db_column_name_required' => 'A column name is required for that operation.',
	'db_column_definition_required' => 'A column definition is required for that operation.',
	'db_unable_to_set_charset' => 'Unable to set client connection character set: %s',
	'db_error_heading' => 'A Database Error Occurred',

);