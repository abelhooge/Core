<?php

namespace Module\DatabaseUtils;
use \FuzeWorks\Module;
use \FuzeWorks\DatabaseException;


/**
 * Class Query
 * @package Module\DatabaseUtils
 *
 * @method $this or() or(string $field, string $arg2) OR $arg2 is the value of the field, or an operator in which case the value is pushed to the third argument
 * @method $this and() and(string $field, string $arg2) AND $arg2 is the value of the field, or an operator in which case the value is pushed to the third argument
 */
class Query extends Module {

    /**
     * @var string The string containing the query
     */
    private $query = '';

    /**
     * @var array The binds corresponding to the query
     */
    private $binds = array();

    /**
     * @var null|string The table used
     */
    private $table = '';

    /**
     * @var null|\PDOStatement
     */
    private $sth = null;

    public function __construct(&$core){

        parent::__construct($core);
    }

    /**
     * Default module function on creation
     */
    public function onLoad() {}

    /**
     * Each argument is another field to select
     *
     * @return $this
     */
    public function select()
    {
        $this->query .= 'SELECT ' . (func_num_args() == 0 ? '*' : '`' . implode("`, `", func_get_args()) . '`');

        return $this;
    }

    /**
     * From
     *
     * @param $table null|string The table, if it is null, the table set with setTable() will be used
     * @return $this
     * @throws Exception
     */
    public function from($table = '')
    {

        if($table === ''){

            if($this->table == '')
                throw new Exception("No table given");

            $table = $this->table;
        }

        $this->query .= ' FROM `' . $table . '`';

        return $this;
    }

    /**
     * Where
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function where($field, $arg2, $arg3 = null){

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        switch($operator){

            case 'IN':

                $this->query .= ' WHERE `' . $field .'` IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' WHERE `' . $field .'` BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' WHERE `' . $field .'` ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * Where open. Is the start of WHERE(....). To end this call ->close()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function where_open($field, $arg2, $arg3 = null){

        $old = $this->query;
        $this->where($field, $arg2, $arg3);
        //Replace the WHERE with WHERE (
        $this->query =  $old . ' WHERE (' . substr($this->query, strlen($old) + 7);

        return $this;
    }

    /**
     * Or, this function should be called after ->where() or ->having(). Please use ->or as an alias instead of ->_or()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function _or($field, $arg2, $arg3 = null){

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        switch($operator){

            case 'IN':

                $this->query .= ' OR `' . $field .'` IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' OR `' . $field .'` BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' OR `' . $field .'` ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * Or open. Is the start of OR(....). To end this call ->close()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function or_open($field, $arg2, $arg3 = null){

        $old = $this->query;
        $this->_or($field, $arg2, $arg3);
        //Replace the OR with OR (
        $this->query =  $old . ' OR (' . substr($this->query, strlen($old) + 4);

        return $this;
    }

    /**
     * And, this function should be called after ->where() or ->having(). Please use ->and as an alias instead of ->_and()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function _and($field, $arg2, $arg3 = null){

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        switch($operator){

            case 'IN':

                $this->query .= ' AND `' . $field .'` IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' AND `' . $field .'` BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' AND `' . $field .'` ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * And open. Is the start of AND(....). To end this call ->close()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function and_open($field, $arg2, $arg3 = null){

        $old = $this->query;
        $this->_and($field, $arg2, $arg3);
        //Replace the AND with AND (
        $this->query =  $old . ' AND (' . substr($this->query, strlen($old) + 5);

        return $this;
    }

    /**
     * Closes and ->where_open() or ->having_open()
     * @return $this
     */
    public function close(){

        $this->query .= ')';
        return $this;
    }

    /**
     * Order By
     *
     * Each argument is another order. If you put a minus in front of the name, the order will be DESC instead of ASC
     *
     * @return $this
     */
    public function order(){

        $this->query .= ' ORDER BY';

        foreach(func_get_args() as $field){

            if(substr($this->query, -2) != 'BY')
                $this->query .= ",";

            $mode = 'ASC';

            if(substr($field, 0, 1) == '-'){

                $field = substr($field, 1);
                $mode = 'DESC';

            }

            $this->query .= ' `' . $field . '` ' . $mode;
        }

        return $this;
    }

    /**
     * limit
     *
     * @param $limit int Limit
     * @param int $offset int Offset
     * @return $this
     */
    public function limit($limit, $offset = 0){

        $this->query .= ' LIMIT ' . $offset . ', ' . $limit;

        return $this;
    }

    /**
     * Having
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function having($field, $arg2, $arg3 = null){

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        switch($operator){

            case 'IN':

                $this->query .= ' HAVING `' . $field .'` IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' HAVING `' . $field .'` BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' HAVING `' . $field .'` ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * Having open. Is the start of HAVING(....). To end this call ->close()
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */

    public function having_open($field, $arg2, $arg3 = null){

        $old = $this->query;
        $this->having($field, $arg2, $arg3);
        //Replace the WHERE with WHERE (
        $this->query =  $old . ' HAVING (' . substr($this->query, strlen($old) + 8);

        return $this;
    }

    /**
     * Update
     *
     * @param $table null|string Name of the table, if it is null, the table set with setTable() will be used
     * @return $this
     * @throws Exception
     */

    public function update($table = '')
    {
        if($table === ''){

            if($this->table == '')
                throw new Exception("No table given");

            $table = $this->table;
        }

        $this->query .= 'UPDATE `' . $table . '`';

        return $this;
    }

    /**
     * Set
     *
     * @param $array array Key value, $field => $value
     * @return $this
     */
    public function set($array)
    {

        $this->query .= ' SET';

        $first = true;

        foreach($array as $field => $value){

            if(!$first)
                $this->query .= ',';

            $first = false;
            $this->query .= ' `' . $field . '`=?';

            $this->binds[] = $value;
        }

        return $this;
    }

    /**
     * Delete
     *
     * @return $this
     */
    public function delete()
    {

        $this->query .= 'DELETE';

        return $this;
    }

    /**
     * Insert
     *
     * @param $array array Key value, $field => $value
     * @param $table string|null Table name, if it is null, the table set with setTable() will be used
     * @return $this
     * @throws Exception
     */
    public function insert($array, $table = ''){

        if($table === ''){

            if($this->table == '')
                throw new DatabaseException("No table given");

            $table = $this->table;
        }

        //Implode the array to get a list with the fields
        $this->query .= 'INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($array)) . '`) VALUES (';

        //Add all the values as ? and add them to the binds
        foreach($array as $field => $value){
            $this->query .= '?,';

            $this->binds[] = $value;
        }

        $this->query = rtrim($this->query, ',') . ')';

        return $this;
    }

    /**
     * Returns the query
     *
     * @return string
     */
    public function getQuery(){

        return $this->query;
    }

    /**
     * Return the binds that corresponds with the binds
     *
     * @return array
     */
    public function getBinds(){

        return $this->binds;
    }

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table){

        $this->table = $table;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTable(){

        return $this->table;
    }

    /**
     * @return $this
     */
    public function commit(){

        $this->mods->database->commit();

        return $this;
    }

    /**
     * @return $this
     */
    public function beginTransaction(){

        $this->mods->database->beginTransaction();

        return $this;
    }

    /**
     * @return $this
     */
    public function rollback(){

        $this->mods->database->rollback();

        return $this;
    }

    /**
     * Executes the query generated
     *
     * @return $this
     * @throws DatabaseException
     */
    public function execute(){


        if($this->config->database->debug)
            $this->logger->log("Generated query: ".$this->query, 'QueryBuilder');

        try{

            $this->sth = $this->mods->database->prepare($this->query);
            if(count($this->binds) === 0){

                $this->sth->execute();
            }else{

                $this->sth->execute($this->binds);
            }
        }catch (\PDOException $e) {

            throw new DatabaseException('Could not execute SQL-query due PDO-exception ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Returns the results of the query as an associative array. ->execute() must be called first.
     * @return array
     * @throws DatabaseException
     */
    public function getArray(){

        if(!isset($this->sth))
            $this->execute();

        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns the results of the query as an array containing objects. ->execute() must be called first.
     * @return object[]
     * @throws DatabaseException
     */
    public function getObject(){

        if(!isset($this->sth))
            $this->execute();

        return $this->sth->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Returns the amount of rows that are affected. ->execute must be called first.
     * @return int Amount of rows that are affected
     * @throws DatabaseException
     */
    public function getRowCount(){

        if(!isset($this->sth))
            $this->execute();

        return $this->sth->rowCount();
    }

    /**
     * @return string
     */
    public function getLastInsertId(){

        return $this->mods->database->lastInsertId();

    }

    /**
     * PHP does not allow to use "or" and "and" as function name, by using __call we can redirect them to _or and _and
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     */
    public function __call($name, $arguments){

        switch($name){

            case "or":
                return call_user_func_array(array($this, "_or"), $arguments);

            case "and":
                return call_user_func_array(array($this, "_and"), $arguments);
        }
    }
}