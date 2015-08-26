<?php

namespace Module\DatabaseUtils;
use \FuzeWorks\Module;
use \FuzeWorks\Bus;
use \FuzeWorks\ModelServer;
use \FuzeWorks\DatabaseException;

class Main extends Module implements ModelServer {

    public $fields  = array();
	public $primary = 'id';
    public $table   = '';

    public function __construct(&$core){
        parent::__construct($core);
    }

    public function onLoad() {
    	require_once($this->getModulePath() . '/class.query.php');
    }

    public function giveModel($type) {
    	return new Model($this->core);
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
 * @package Module\DatabaseUtils
 */
class Model extends Bus {

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

		if($this->config->database->debug)
			$this->logger->log("Manuel Query: ".$query, "Database Model");

		try{

			$sth = $this->mods->database->prepare($query);
			if($binds === null){

				$sth->execute();
			}else{

				$sth->execute($binds);
			}
		}catch (\PDOException $e){

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
	 * The default table will be set to $this->table
	 * @see Query::select
	 * @return Query
	 */
	public function select(){

		$queryBuilder = new Query($this->core);
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

		$queryBuilder = new Query($this->core);
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'update'), func_get_args());

		return $queryBuilder;
	}

	/**
	 * The default table will be set to $this->table
	 * @see Query::delete
	 * @return Query
	 */

	public function delete(){

		$queryBuilder = new Query($this->core);
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'delete'), func_get_args());

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

		$queryBuilder = new Query($this->core);
		$queryBuilder->setTable($this->table);
        call_user_func_array(array($queryBuilder, 'insert'), func_get_args());

		return $queryBuilder;
	}

	/**
	 * Return latest insert id
	 *
	 * @return mixed
	 */
	public function getLastInsertId(){
	
		return $this->mods->database->lastInsertId();
	}

	public function __call($name, $params) {
		return call_user_func_array(array($this->mods->database, $name), $params);
	}
}