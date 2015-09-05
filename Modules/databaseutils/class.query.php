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
use \FuzeWorks\DatabaseException;
use \FuzeWorks\Config;
use \FuzeWorks\Logger;


/**
 * Class Query
 * @package     net.techfuze.fuzeworks.databaseutils
 * @author      GOScripting
 * @copyright   Copyright (c) 2014 - 2015, GOScripting B.V. (http://goscripting.com)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 * @link        http://goframework.net
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
     * @param ...$table null|string The table, if it is null, the table set with setTable() will be used
     * @return $this
     * @throws Exception
     */
    public function from()
    {
        $tables = func_get_args();

        $this->query .= ' FROM ';

        if(count($tables) == 0)
            $tables[] = "`$this->table`";

        else foreach($tables as $i => $t) {

            if ($t == '' && $this->table == '')
                throw new Exception("No table given");

            $tables[$i] = strpos($t, ' ') === false ? "`$t`" : '`'.implode('` ', explode(' ', $t));
        }

        $this->query .= implode(', ', $tables);

        return $this;
    }

    /**
     * (Inner) join
     *
     * @param string $table The table to join
     * @param string $type The type of join to perform (JOIN, LEFT JOIN, RIGHT JOIN, FULL JOIN)
     * @return $this
     * @throws Exception
     */
    public function join($table, $type = ''){

        if($table === '') {

            if ($this->table == '')
                throw new Exception("No table given");

            $table = $this->table;
        }

        if($type != '')
            $this->query .= ' '.$type;

        $this->query .= ' JOIN ' . (strpos($table, ' ') === false ? "`$table`" : '`'.implode('` ', explode(' ', $table)));

        return $this;
    }

    /**
     * Left join
     *
     * @param string $table The table to join
     * @return $this
     * @throws Exception
     */
    public function left_join($table){

        return $this->join($table, 'LEFT');
    }

    /**
     * Right join
     *
     * @param string $table The table to join
     * @return $this
     * @throws Exception
     */
    public function right_join($table){

        return $this->join($table, 'RIGHT');
    }

    /**
     * Full join
     *
     * @param string $table The table to join
     * @return $this
     * @throws Exception
     */
    public function full_join($table){

        return $this->join($table, 'FULL');
    }

    /**
     * On
     *
     * @param string $field Field name, or raw SQL
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    public function on($field, $arg2 = null, $arg3 = null){

        return $this->where($field, $arg2, $arg3, 'ON');
    }

    /**
     * Where
     *
     * @param string $field Field name, or raw SQL
     * @param string $arg2, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @param string $type Whether this is an WHERE or ON operation
     * @return $this
     */
    public function where($field, $arg2 = null, $arg3 = null, $type = 'WHERE'){

        if($arg2 === null)
            return $this->sql(' '.$type.' '.$field);

        //The value is the second parameter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        $field = $this->formatField($field);

        switch($operator){

            case 'IN':

                $this->query .= ' '.$type.' '.$field.' IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';

                break;

            case 'BETWEEN':

                $this->query .= ' '.$type.' '.$field.' BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' '.$type.' '.$field.' ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * Where open. Is the start of WHERE(....). To end this call ->close()
     *
     * @param string $field Field name, or raw SQL
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
     * @param string $field Field name, or raw SQL
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    private function _or($field, $arg2 = null, $arg3 = null){

        if($arg2 === null)
            return $this->sql(' OR '.$field);

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        $field = $this->formatField($field);

        switch($operator){

            case 'IN':

                $this->query .= ' OR ' . $field .' IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' OR ' . $field .' BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' OR ' . $field .' ' . $operator . ' ?';
                $this->binds[] = $value;

                break;
        }
        return $this;
    }

    /**
     * Or open. Is the start of OR(....). To end this call ->close()
     *
     * @param string $field Field name, or raw SQL
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
     * @param string $field Field name, or raw SQL
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     * @return $this
     */
    private function _and($field, $arg2 = null, $arg3 = null){

        if($arg2 === null)
            return $this->sql(' AND '.$field);

        //The value is the second paramter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? "=" : $arg2);

        $field = $this->formatField($field);

        switch($operator){

            case 'IN':

                $this->query .= ' AND ' . $field .' IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' AND ' . $field .' BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' AND ' . $field .' ' . $operator . ' ?';
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

            $field = $this->formatField($field);

            $this->query .= ' ' . $field . ' ' . $mode;
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

        $field = $this->formatField($field);

        switch($operator){

            case 'IN':

                $this->query .= ' HAVING ' . $field .' IN (';

                foreach($value as $k => $v){

                    $this->query .= '?,';
                    $this->binds[] = $v;
                }

                //Remove the trailing comma and close it
                $this->query = rtrim($this->query, ",") . ')';


                break;

            case 'BETWEEN':

                $this->query .= ' HAVING ' . $field .' BETWEEN ? AND ?';
                $this->binds[] = $value[0];
                $this->binds[] = $value[1];

                break;

            default:

                $this->query .= ' HAVING ' . $field .' ' . $operator . ' ?';
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
     * @param $data array|string Key value, $field => $value or raw SQL
     * @return $this
     */
    public function set($data)
    {
        if(is_string($data))
            return $this->sql(' SET '.$data);

        $this->query .= ' SET';

        $first = true;

        foreach($data as $field => $value){

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
     * Add raw SQL to the query string
     *
     * @param string $sql The SQL to add
     * @return string
     */
    public function sql($sql){

        $this->query .= $sql;

        return $this;
    }

    /**
     * Formats the given field
     *
     * @param string $field The field to format
     * @return string The formatted field
     */
    private function formatField($field){

        if(strpos($field, '.') === false)
            $field = "`$field`";

        return $field;
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

        Modules::get('core/database')->commit();

        return $this;
    }

    /**
     * @return $this
     */
    public function beginTransaction(){

        Modules::get('core/database')->beginTransaction();

        return $this;
    }

    /**
     * @return $this
     */
    public function rollback(){

        Modules::get('core/database')->rollback();

        return $this;
    }

    /**
     * Executes the query generated
     *
     * @return $this
     * @throws DatabaseException
     */
    public function execute(){


        if(Config::get('database')->debug)
            Logger::log("Generated query: ".$this->query, 'QueryBuilder');

        try{

            $this->sth = Modules::get('core/database')->prepare($this->query);
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

        return Modules::get('core/database')->lastInsertId();

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