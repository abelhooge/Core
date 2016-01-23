<?php
/**
 * FuzeWorks
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
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 * @version     Version 0.0.1
 */


namespace Module\DatabaseUtils;
use \FuzeWorks\Module;
use \FuzeWorks\Modules;
use \FuzeWorks\Bus;
use \FuzeWorks\ModelServer;
use \FuzeWorks\DatabaseException;
use \FuzeWorks\Config;
use \FuzeWorks\Logger;
use \PDOException;

/**
 * Main class of the database utilities and model providers
 * @package     net.techfuze.fuzeworks.databaseutils
 * @author      Abel Hoogeveen <abel@techfuze.net>
 * @copyright   Copyright (c) 2013 - 2015, Techfuze. (http://techfuze.net)
 */
class Main implements ModelServer {
	use Module;

    public $fields  = array();
	public $primary = 'id';
    public $table   = '';

    public function onLoad() {
    	require_once(self::getModulePath() . '/class.query.php');
    }

    public function giveModel($type) {
    	return new Model();
    }
}

/**
 * Class Model
 *
 * Models provide an easy connection to database tables. Each table requires its own model. You don't need to worry about your queries or syntax anymore, because
 * models will handle queries and error handling. Because of this, you can freely change your database infrastructure without fear of needing to change your
 * table names at thousands of places. The model is the only place where you have to change your database names or fields.
 *
 * Models also allow custom methods to be created on them. You can use those methods to create specific operations or joins and then use the newly created method
 * everywhere in your project. The code is at one place, the usages all over your project. Isn't that great?
 *
 * @package     net.techfuze.fuzeworks.databaseutils
 * @author      GOScripting
 * @copyright   Copyright (c) 2014 - 2015, GOScripting B.V. (http://goscripting.com)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://goframework.net
 */
class Model {

	/**
	 * @var string The name of the database table
	 */
	public $table   = '';

	/**
	 * Traditional query interface
	 *
	 * @param string $query
	 * @param null $binds
	 * @return mixed returns fetched rows if available, otherwise returns number of affected rows
	 * @throws DatabaseException
	 */
	public function query($query, $binds = null){

		if(Config::get('database')->debug)
			Logger::log("Manuel Query: ".$query, "Database Model");

		try{

			$sth = Modules::get('core/database')->prepare($query);
			if($binds === null){

				$sth->execute();
			}else{

				$sth->execute($binds);
			}
		}catch (PDOException $e){

			throw new DatabaseException('Could not execute SQL-query due PDO-exception '.$e->getMessage());
		}

        if($sth->columnCount() > 0){

            // Fetch results
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }else{

            // Fetch number of affected rows
            $result = $sth->rowCount();
        }

		return $result;
	}

	/**
	 * Set the table you wish to approach
	 * @param String $table Table name
	 */
	public function setTable($table) {
		$this->table = $table;
		return $this;
	}

	/**
	 * The default table will be set to $this->table
	 * @see Query::select
	 * @return Query
	 */
	public function select(){

		$queryBuilder = new Query();
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'select'), func_get_args());
		$queryBuilder->from();

		return $queryBuilder;
	}

	/**
	 * The default table will be set to $this->table
	 * @see Query::update
	 * @return Query
	 */

	public function update(){

		$queryBuilder = new Query();
		$queryBuilder->setTable($this->table);
		$queryBuilder->update($this->table);

		return $queryBuilder;
	}

	/**
	 * The default table will be set to $this->table
	 * @see Query::delete
	 * @return Query
	 */

	public function delete(){

		$queryBuilder = new Query();
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'delete'), func_get_args());
        $queryBuilder->from();

		return $queryBuilder;
	}

	/**
	 * The default table will be set to $this->table
	 * @see Query::insert
	 * @param $array Array with values
	 * @return Query
	 * @throws Exception
	 */

	public function insert($array){

		$queryBuilder = new Query();
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'insert'), func_get_args());

		return $queryBuilder;
	}

    /**
     * The default table will be set to $this->table
     * @see Query::replace
     * @param $array Array with values
     * @return Query
     * @throws Exception
     */

    public function replace($array){

        $queryBuilder = new Query();
        $queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'replace'), func_get_args());

        return $queryBuilder;
    }

	/**
	 * Return latest insert id
	 *
	 * @return mixed
	 */
	public function getLastInsertId(){
		return Modules::get('core/database')->lastInsertId();
	}

	public function __call($name, $params) {
		return call_user_func_array(array(Modules::get('core/database'), $name), $params);
	}
}