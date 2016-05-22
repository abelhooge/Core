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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */
use \FuzeWorks\Core;
use \FuzeWorks\Models;

/**
 * Class ModelTest.
 *
 * Core model testing suite, will test basic model functionality
 */
class modelTest extends CoreTestAbstract
{
    /**
     * Select.
     */
    public function testSelectSimple()
    {
        $query = Models::get('sqltable')->select();
        $this->assertEquals('SELECT * FROM table', $query->getSql());
    }

    public function testSelectSimpleOneField()
    {
        $query = Models::get('sqltable')->select('field1');
        $this->assertEquals('SELECT field1 FROM table', $query->getSql());
    }

    public function testSelectSimpleTwoFields()
    {
        $query = Models::get('sqltable')->select('field1', 'field2');
        $this->assertEquals('SELECT field1, field2 FROM table', $query->getSql());
    }

    /**
     * Delete.
     */
    public function testDeleteSimple()
    {
        $query = Models::get('sqltable')->delete()->from('table');
        $this->assertEquals('DELETE FROM table', $query->getSql());
    }

    /**
     * Insert.
     */
    public function testInsertSimple()
    {
        $query = Models::get('sqltable')->insert(array('field' => 'value'));
        $this->assertEquals('INSERT INTO table (field) VALUES (?)', $query->getSql());
        $this->assertEquals(array('value'), $query->getBinds());
    }

    public function testInsertMultiple()
    {
        $query = Models::get('sqltable')->insert(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('INSERT INTO table (field1,field2) VALUES (?,?)', $query->getSql());
        $this->assertEquals(array('value1', 'value2'), $query->getBinds());
    }

    /**
     * Replace.
     */
    public function testReplaceSimple()
    {
        $query = Models::get('sqltable')->replace(array('field' => 'value'));
        $this->assertEquals('REPLACE INTO table (field) VALUES (?)', $query->getSql());
        $this->assertEquals(array('value'), $query->getBinds());
    }

    public function testReplaceMultiple()
    {
        $query = Models::get('sqltable')->replace(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('REPLACE INTO table (field1,field2) VALUES (?,?)', $query->getSql());
        $this->assertEquals(array('value1', 'value2'), $query->getBinds());
    }
}
