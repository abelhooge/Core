<?php
use \FuzeWorks\Core;
use \FuzeWorks\Models;

/**
 * Class ModelTest
 *
 * Core model testing suite, will test basic model functionality
 *
 */
class ModelTest extends CoreTestAbstract
{
    /**
     * Select
     */

    public function testSelectSimple()
    {
        $query = Models::get('sqltable')->select();
        $this->assertEquals('SELECT * FROM table', $query->getSql());
    }

    public function testSelectSimpleOneField(){

        $query = Models::get('sqltable')->select('field1');
        $this->assertEquals('SELECT field1 FROM table', $query->getSql());
    }

    public function testSelectSimpleTwoFields(){

        $query = Models::get('sqltable')->select('field1', 'field2');
        $this->assertEquals('SELECT field1, field2 FROM table', $query->getSql());
    }

    /**
     * Delete
     */

    public function testDeleteSimple(){

        $query = Models::get('sqltable')->delete()->from('table');
        $this->assertEquals('DELETE FROM table', $query->getSql());
    }

    /**
     * Insert
     */

    public function testInsertSimple(){

        $query = Models::get('sqltable')->insert(array('field' => 'value'));
        $this->assertEquals('INSERT INTO table (field) VALUES (?)', $query->getSql());
        $this->assertEquals(array('value'), $query->getBinds());
    }

    public function testInsertMultiple(){

        $query = Models::get('sqltable')->insert(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('INSERT INTO table (field1,field2) VALUES (?,?)', $query->getSql());
        $this->assertEquals(array('value1', 'value2'), $query->getBinds());
    }

    /**
     * Replace
     */

    public function testReplaceSimple(){

        $query = Models::get('sqltable')->replace(array('field' => 'value'));
        $this->assertEquals('REPLACE INTO table (field) VALUES (?)', $query->getSql());
        $this->assertEquals(array('value'), $query->getBinds());
    }

    public function testReplaceMultiple(){

        $query = Models::get('sqltable')->replace(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('REPLACE INTO table (field1,field2) VALUES (?,?)', $query->getSql());
        $this->assertEquals(array('value1', 'value2'), $query->getBinds());
    }

}