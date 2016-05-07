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

namespace Module\DatabaseUtils;

use FuzeWorks\Modules;
use FuzeWorks\DatabaseException;
use FuzeWorks\Config;
use FuzeWorks\Logger;

/**
 * Class Query.
 *
 * @author    GOScripting
 * @copyright Copyright (c) 2014 - 2015, GOScripting B.V. (http://goscripting.com)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link http://goframework.net
 *
 * @method $this or() or(string $field, string $arg2) OR $arg2 is the value of the field, or an operator in which case the value is pushed to the third argument
 * @method $this and() and(string $field, string $arg2) AND $arg2 is the value of the field, or an operator in which case the value is pushed to the third argument
 */
class Query
{
    /**
     * @var array An array containing all the counted functions
     */
    private $functions = array();

    /**
     * @var null|string The table used
     */
    private $table = '';

    /**
     * @var null|\PDOStatement
     */
    private $sth = null;

    /**
     * @param string $table The string to set as a table, optional
     */
    public function __construct($table = '')
    {
        if ($table != '') {
            $this->setTable($table);
        }
    }
    /**
     * Each argument is another field to select.
     *
     * @param string $field,... The field to select, if no field is given, all fields(*) will be selected
     *
     * @return $this
     */
    public function select($field = '*')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalSelect($field = '*')
    {
        return array(
            'sql' => 'SELECT '.($field == '*' ? '*' :  implode(', ', func_get_args()).''),
            'binds' => null,
        );
    }

    /**
     * From.
     *
     * @param $table,... null|string The table, if it is null, the table set with setTable() will be used
     *
     * @return $this
     *
     * @throws Exception
     */
    public function from($table = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalFrom($table = null)
    {
        $tables = func_get_args();

        $query = 'FROM ';

        if (count($tables) == 0) {
            $tables[] = "$this->table";
        } else {
            foreach ($tables as $i => $t) {
                if ($t == '' && $this->table == '') {
                    throw new Exception('No table given');
                }

                $tables[$i] = strpos($t, ' ') === false ? "$t" : ''.implode(' ', explode(' ', $t));
            }
        }

        $query .= implode(', ', $tables);

        return array(
            'sql' => $query,
            'binds' => null,
        );
    }

    /**
     * (Inner) join.
     *
     * @param string $table The table to join
     * @param string $type  The type of join to perform (JOIN, LEFT JOIN, RIGHT JOIN, FULL JOIN)
     *
     * @return $this
     *
     * @throws Exception
     */
    public function join($table, $type = '')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalJoin($table, $type = '')
    {
        if ($table === '') {
            if ($this->table == '') {
                throw new Exception('No table given');
            }

            $table = $this->table;
        }

        $query = '';

        if ($type != '') {
            $query .= $type.' ';
        }

        $query .= 'JOIN '.(strpos($table, ' ') === false ? "$table" : ''.implode(' ', explode(' ', $table)));

        return array(
            'sql' => $query,
            'binds' => null,
        );
    }

    /**
     * Left join.
     *
     * @param string $table The table to join
     *
     * @return $this
     *
     * @throws Exception
     */
    public function left_join($table)
    {
        return $this->join($table, 'LEFT');
    }

    /**
     * Right join.
     *
     * @param string $table The table to join
     *
     * @return $this
     *
     * @throws Exception
     */
    public function right_join($table)
    {
        return $this->join($table, 'RIGHT');
    }

    /**
     * Full join.
     *
     * @param string $table The table to join
     *
     * @return $this
     *
     * @throws Exception
     */
    public function full_join($table)
    {
        return $this->join($table, 'FULL');
    }

    /**
     * On.
     *
     * @param string $field Field name, or raw SQL
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     *
     * @return $this
     */
    public function on($field, $arg2 = null, $arg3 = null)
    {
        return $this->where($field, $arg2, $arg3, 'ON');
    }

    /**
     * Where.
     *
     * @param string|null $field Field name, or raw SQL, If this is left empty, only the $type will be added to the query
     * @param string      $arg2, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3  The value of the field when $arg2 is used for an operator
     * @param string      $type  Whether this is an WHERE or ON operation
     *
     * @return $this
     */
    public function where($field = null, $arg2 = null, $arg3 = null, $type = 'WHERE')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalWhere($field = null, $arg2 = null, $arg3 = null, $type = 'WHERE')
    {
        if ($field == null) {
            return array('sql' => $type, 'binds' => array());
        }

        if ($arg2 === null) {
            return $this->internalSql($type.' '.$field);
        }

        //The value is the second parameter, unless the second parameter is an operator, in which case it's the third
        $value = ($arg3 == null ? $arg2 : $arg3);
        //If the third parameter is not given, the default operator should be used
        $operator = strtoupper($arg3 == null ? '=' : $arg2);

        $query = '';
        $binds = array();

        switch ($operator) {
            case 'IN':
                $query .= $type.($type == '' ? '' : ' ').$field.' IN (';

                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $query .= '?,';
                        $binds[] = $v;
                    }

                    //Remove the trailing comma and close it
                    $query = rtrim($query, ',').')';
                } elseif (get_class($value) == 'System\Core\Query') {
                    /* @var Query $value */
                    $data = $this->internalSql($value->getSql(), $value->getBinds());
                    $query .= $data['sql'].')';
                    $binds = array_merge($binds, $data['binds']);
                }
                break;

            case 'BETWEEN':
                $query .= $type.($type == '' ? '' : ' ').$field.' BETWEEN ? AND ?';
                $binds[] = $value[0];
                $binds[] = $value[1];

                break;

            default:
                $query .= $type.($type == '' ? '' : ' ').$field.' '.$operator.' ?';
                $binds[] = $value;

                break;
        }

        return array(
            'sql' => $query,
            'binds' => $binds,
        );
    }

    /**
     *And, keep in mind this is only the OR statement. For A = B call ->where.
     *
     * @return array
     */
    private function internalOr()
    {
        return array(
            'sql' => 'OR',
            'binds' => array(),
        );
    }

    /**
     * And, keep in mind this is only the AND statement. For A = B call ->where.
     *
     * @return array
     */
    private function internalAnd()
    {
        return array(
            'sql' => 'AND',
            'binds' => array(),
        );
    }

    /**
     * An opening bracket.
     *
     * @return $this
     */
    public function open()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalOpen()
    {
        return array(
            'sql' => '(',
            'binds' => '',
        );
    }

    /**
     * A closing bracket.
     *
     * @return $this
     */
    public function close()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalClose()
    {
        return array(
            'sql' => ')',
            'binds' => null,
        );
    }

    /**
     * Order By.
     *
     * Each argument is another order. If you put a minus in front of the name, the order will be DESC instead of ASC
     *
     * @param string $field,... The field to order by, add a minus in front of the name and the order will be DESC
     *
     * @return $this
     */
    public function order($field)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalOrder($field)
    {
        $query = 'ORDER BY';

        foreach (func_get_args() as $field) {
            if (substr($query, -2) != 'BY') {
                $query .= ',';
            }

            $mode = 'ASC';

            if (substr($field, 0, 1) == '-') {
                $field = substr($field, 1);
                $mode = 'DESC';
            }

            $query .= ' '.$field.' '.$mode;
        }

        return array(
            'sql' => $query,
            'binds' => null,
        );
    }

    /**
     * Group by.
     *
     * Each argument is another field to group by.
     *
     * @param string $field,... The field to group by
     *
     * @return $this
     */
    public function groupBy($field)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalGroupBy($field)
    {
        $query = 'GROUP BY ';

        $first = true;

        foreach (func_get_args() as $field) {
            if (!$first) {
                $query .= ', ';
            }

            $first = false;

            $query .= $field;
        }

        return array(
            'sql' => $query,
            'binds' => null,
        );
    }

    /**
     * limit.
     *
     * @param $limit int Limit
     * @param int $offset int Offset
     *
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalLimit($limit, $offset = 0)
    {
        return array(
            'sql' => 'LIMIT '.$offset.', '.$limit,
            'binds' => null,
        );
    }

    /**
     * Having.
     *
     * @param $field
     * @param $arg2 string, The value of the field, or an operator in which case the value is pushed to $arg3
     * @param null|string $arg3 The value of the field when $arg2 is used for an operator
     *
     * @return $this
     */
    public function having($field = null, $arg2 = null, $arg3 = null)
    {
        return $this->where($field, $arg2, $arg3, 'HAVING');
    }

    /**
     * Update.
     *
     * @param $table null|string Name of the table, if it is null, the table set with setTable() will be used
     *
     * @return $this
     *
     * @throws Exception
     */
    public function update($table = '')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalUpdate($table = '')
    {
        if ($table === '') {
            if ($this->table == '') {
                throw new Exception('No table given');
            }

            $table = $this->table;
        }

        return array(
            'sql' => 'UPDATE '.$table,
            'binds' => array(),
        );
    }

    /**
     * Set.
     *
     * @param $data array|string Key value, $field => $value or raw SQL
     *
     * @return $this
     */
    public function set($data)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalSet($data)
    {
        if (is_string($data)) {
            return $this->internalSql('SET '.$data);
        }

        $query = 'SET';
        $binds = array();

        $first = true;

        foreach ($data as $field => $value) {
            if (!$first) {
                $query .= ',';
            }

            $first = false;
            $query .= ' '.$field.'=?';

            $binds[] = $value;
        }

        return array(
            'sql' => $query,
            'binds' => $binds,
        );
    }

    /**
     * Delete.
     *
     * @return $this
     */
    public function delete()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalDelete()
    {
        return array(
            'sql' => 'DELETE',
            'binds' => array(),
        );
    }

    /**
     * Insert.
     *
     * @param array array Key value, $field => $value
     * @param $table string|null Table name, if it is null, the table set with setTable() will be used
     *
     * @return $this
     *
     * @throws Exception
     */
    public function insert($array, $table = '')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalInsert($array, $table = '')
    {
        if ($table === '') {
            if ($this->table == '') {
                throw new DatabaseException('No table given');
            }

            $table = $this->table;
        }

        $query = '';
        $binds = array();

        //Implode the array to get a list with the fields
        $query .= 'INSERT INTO '.$table.' ('.implode(',', array_keys($array)).') VALUES (';

        //Add all the values as ? and add them to the binds
        foreach ($array as $field => $value) {
            $query .= '?,';

            $binds[] = $value;
        }

        $query = rtrim($query, ',').')';

        return array(
            'sql' => $query,
            'binds' => $binds,
        );
    }

    /**
     * Replace.
     *
     * @param array array Key value, $field => $value
     * @param $table string|null Table name, if it is null, the table set with setTable() will be used
     *
     * @return $this
     *
     * @throws Exception
     */
    public function replace($array, $table = '')
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalReplace($array, $table = '')
    {
        if ($table === '') {
            if ($this->table == '') {
                throw new DatabaseException('No table given');
            }

            $table = $this->table;
        }

        $query = '';
        $binds = array();

        //Implode the array to get a list with the fields
        $query .= 'REPLACE INTO '.$table.' ('.implode(',', array_keys($array)).') VALUES (';

        //Add all the values as ? and add them to the binds
        foreach ($array as $field => $value) {
            $query .= '?,';

            $binds[] = $value;
        }

        $query = rtrim($query, ',').')';

        return array(
            'sql' => $query,
            'binds' => $binds,
        );
    }

    /**
     * Add raw SQL to the query string.
     *
     * @param string $sql   The SQL to add
     * @param array  $binds The optional binds to add
     *
     * @return $this
     */
    public function sql($sql, $binds = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    private function internalSql($sql, $binds = array())
    {
        return array(
            'sql' => $sql,
            'binds' => $binds,
        );
    }

    /**
     * Builds The SQL query and its binds.
     *
     * @return array
     */
    public function build()
    {
        $sql = array();
        $binds = array();

        foreach ($this->functions as $i => $function) {
            //The current function is a WHERE or HAVING,
            //and it after a or, and or open, this means we do not have to
            //add the WHERE/HAVING statement again
            if (($function['name'] == 'internalWhere'
                || $function['name'] == 'internalHaving')
                && ($this->functions[$i - 1]['name'] == 'internalOpen'
                || $this->functions[$i - 1]['name'] == 'internalAnd'
                || $this->functions[$i - 1]['name'] == 'internalOr')
            ) {
                if (!isset($function['arguments'][2])) {
                    $function['arguments'][2] = null;
                }

                $function['arguments'][3] = '';
            }

            $data = call_user_func_array(array($this, $function['name']), $function['arguments']);

            $sql[] = $data['sql'];

            if ($data['binds'] != null) {
                $binds = array_merge($binds, $data['binds']);
            }
        }

        return array(
            'sql' => implode(' ', $sql),
            'binds' => $binds,
        );
    }
    /**
     * Returns the query as an array.
     *
     * @return array
     */
    public function getSql()
    {
        return $this->build()['sql'];
    }

    /**
     * Return the built SQL query.
     *
     * @return string
     */
    public function getBinds()
    {
        return $this->build()['binds'];
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return $this
     */
    public function commit()
    {
        Modules::get('core/database')->commit();

        return $this;
    }

    /**
     * @return $this
     */
    public function beginTransaction()
    {
        Modules::get('core/database')->beginTransaction();

        return $this;
    }

    /**
     * @return $this
     */
    public function rollback()
    {
        Modules::get('core/database')->rollback();

        return $this;
    }

    /**
     * Executes the query generated.
     *
     * @return $this
     *
     * @throws DatabaseException
     */
    public function execute()
    {
        if (Config::get('database')->debug) {
            Logger::log(get_class($this).': '.$this->getSql());
        }

        try {
            $this->sth = Modules::get('core/database')->prepare($this->getSql());
            if (count($this->getBinds()) === 0) {
                $this->sth->execute();
            } else {
                $this->sth->execute($this->getBinds());
            }
        } catch (\PDOException $e) {
            throw new DatabaseException('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }

        return $this;
    }

    /**
     * Returns the results of the query in the given type. All the arguments of this function will be passed onto
     * fetchAll.
     *
     * @param int $type The default type is \PDO::FETCH_ASSOC, all the types that are possible for fetchAll() are valid
     *
     * @return array
     *
     * @throws DatabaseException
     */
    public function getResults($type = \PDO::FETCH_ASSOC)
    {
        if (!isset($this->sth)) {
            $this->execute();
        }

        return call_user_func_array(
            array(
            $this->sth,
            'fetchAll',
            ),
            func_get_args()
        );
    }

    /**
     * Returns the amount of rows that are affected. ->execute must be called first.
     *
     * @return int Amount of rows that are affected
     *
     * @throws DatabaseException
     */
    public function getRowCount()
    {
        if (!isset($this->sth)) {
            $this->execute();
        }

        return $this->sth->rowCount();
    }

    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return Modules::get('core/database')->lastInsertId();
    }

    /**
     * All the called functions are saved in an array, so when build is called, the query is built from all the functions.
     *
     * @param $name string
     * @param $arguments array
     *
     * @return $this
     *
     * @throws DatabaseException
     */
    public function __call($name, $arguments)
    {
        $name = 'internal'.ucfirst($name);

        if (!method_exists($this, $name)) {
            throw new DatabaseException('Unknown function "'.$name.'"');
        }

        $this->functions[] = array(
            'name' => $name,
            'arguments' => $arguments,
        );

        return $this;
    }
}
