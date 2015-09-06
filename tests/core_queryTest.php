<?php
use \FuzeWorks\Modules;
use \Module\DatabaseUtils\Query;

class QueryTests extends \CoreTestAbstract {

    /**
     * @var Query
     */
    public $query;

    public $core;

    /**
     * @before
     */
    protected function setUp(){
        Modules::get('core/databaseutils');
        $this->query = new Query();
    }

    public function testConstructor(){

        $query = new Query('table');
        $this->assertEquals('table', $query->getTable());

    }
    /*
     * Select
     */

    public function testSelectSimple()
    {
        $this->query->select()->from('table');
        $this->assertEquals('SELECT * FROM table', $this->query->getSql());
    }

    public function testSelectSimpleDefaultTable(){

        $this->query->setTable("table")->select()->from();
        $this->assertEquals('SELECT * FROM table', $this->query->getSql());
    }

    public function testSelectSimpleComboTable()
    {
        $this->query->select()->from('table', 'table2');
        $this->assertEquals('SELECT * FROM table, table2', $this->query->getSql());
    }

    public function testSelectSimpleAlias()
    {
        $this->query->select()->from('table t');
        $this->assertEquals('SELECT * FROM table t', $this->query->getSql());
    }

    public function testSelectSimpleComboAlias()
    {
        $this->query->select()->from('table t', 'table2 t2');
        $this->assertEquals('SELECT * FROM table t, table2 t2', $this->query->getSql());
    }

    public function testSelectSimpleOneField(){

        $this->query->select('field1')->from('table');
        $this->assertEquals('SELECT field1 FROM table', $this->query->getSql());
    }

    public function testSelectSimpleTwoFields(){

        $this->query->select('field1', 'field2')->from('table');
        $this->assertEquals('SELECT field1, field2 FROM table', $this->query->getSql());
    }

    /*
     * Where
     */

    public function testSelectWhere(){

        $this->query->select()->from('table')->where("field", "value");
        $this->assertEquals('SELECT * FROM table WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereLike(){

        $this->query->select()->from('table')->where("field", "like", '%value%');
        $this->assertEquals('SELECT * FROM table WHERE field LIKE ?', $this->query->getSql());
        $this->assertEquals(array('%value%'), $this->query->getBinds());
    }

    public function testSelectWhereBetween(){

        $this->query->select()->from('table')->where("field", "between", array(2, 4));
        $this->assertEquals('SELECT * FROM table WHERE field BETWEEN ? AND ?', $this->query->getSql());
        $this->assertEquals(array(2, 4), $this->query->getBinds());
    }

    public function testSelectWhereIn(){

        $this->query->select()->from('table')->where("field", "in", array(2, 3, 4));
        $this->assertEquals('SELECT * FROM table WHERE field IN (?,?,?)', $this->query->getSql());
        $this->assertEquals(array(2, 3, 4), $this->query->getBinds());
    }

    /**
     * @todo Make this work!
     * @return [type] [description]
     */
    /*public function testSelectWhereInSubQuery(){

        $subQuery = new Query();
        $subQuery->select()->from('table2')->where('field', 'value');

        $this->query->select()->from('table')->where("field", "in", $subQuery);
        $this->assertEquals('SELECT * FROM table WHERE field IN (SELECT * FROM table2 WHERE field = ?)', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }*/

    public function testSelectWhereNot(){

        $this->query->select()->from('table')->where("field", "<>", "value");
        $this->assertEquals('SELECT * FROM table WHERE field <> ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereGreater(){

        $this->query->select()->from('table')->where("field", ">", "value");
        $this->assertEquals('SELECT * FROM table WHERE field > ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereGreaterEqual(){

        $this->query->select()->from('table')->where("field", ">=", "value");
        $this->assertEquals('SELECT * FROM table WHERE field >= ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereSmaller(){

        $this->query->select()->from('table')->where("field", "<", "value");
        $this->assertEquals('SELECT * FROM table WHERE field < ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereSmallerEqual(){

        $this->query->select()->from('table')->where("field", "<=", "value");
        $this->assertEquals('SELECT * FROM table WHERE field <= ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectWhereAnd(){

        $this->query->select()->from('table')->where("field1", "value1")->and()->where("field2", "value2");
        $this->assertEquals('SELECT * FROM table WHERE field1 = ? AND field2 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());
    }

    public function testSelectWhereAndOpen(){

        $this->query->select()->from('table')->where("field1", "value1")->and()->open()->where("field2", "value2")->or()->where("field3", "value3")->close();
        $this->assertEquals('SELECT * FROM table WHERE field1 = ? AND ( field2 = ? OR field3 = ? )', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }

    public function testSelectWhereOr(){

        $this->query->select()->from('table')->where("field1", "value1")->or()->where("field2", "value2");
        $this->assertEquals('SELECT * FROM table WHERE field1 = ? OR field2 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());
    }

    public function testSelectWhereOrOpen(){

        $this->query->select()->from('table')->where("field1", "value1")->or()->open()->where("field2", "value2")->and()->where("field3", "value3")->close();
        $this->assertEquals('SELECT * FROM table WHERE field1 = ? OR ( field2 = ? AND field3 = ? )', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }
    public function testSelectWhereOpen(){

        $this->query->select()->from('table')
            ->where()->open()->where("field1", "value1")->and()->where("field2", "value2")->close()
        ->or()->where('field3', 'value3');
        $this->assertEquals('SELECT * FROM table WHERE ( field1 = ? AND field2 = ? ) OR field3 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }

    /*
     * Order by
     */

    public function testSelectOrderASC(){

        $this->query->select()->from('table')->order('field');
        $this->assertEquals('SELECT * FROM table ORDER BY field ASC', $this->query->getSql());
    }

    public function testSelectOrderDESC(){

        $this->query->select()->from('table')->order('-field');
        $this->assertEquals('SELECT * FROM table ORDER BY field DESC', $this->query->getSql());
    }

    public function testSelectOrderMultiple(){

        $this->query->select()->from('table')->order('field1', '-field2');
        $this->assertEquals('SELECT * FROM table ORDER BY field1 ASC, field2 DESC', $this->query->getSql());
    }

    /*
     * Limit
     */

    public function testSelectLimit()
    {
        $this->query->select()->from('table')->limit(5, 10);
        $this->assertEquals('SELECT * FROM table LIMIT 10, 5', $this->query->getSql());

    }

    /*
     * Having
     */

    public function testSelectHaving(){

        $this->query->select()->from('table')->having("field", "value");
        $this->assertEquals('SELECT * FROM table HAVING field = ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingLike(){

        $this->query->select()->from('table')->having("field", "like", '%value%');
        $this->assertEquals('SELECT * FROM table HAVING field LIKE ?', $this->query->getSql());
        $this->assertEquals(array('%value%'), $this->query->getBinds());
    }

    public function testSelectHavingBetween(){

        $this->query->select()->from('table')->having("field", "between", array(2, 4));
        $this->assertEquals('SELECT * FROM table HAVING field BETWEEN ? AND ?', $this->query->getSql());
        $this->assertEquals(array(2, 4), $this->query->getBinds());
    }

    public function testSelectHavingIn(){

        $this->query->select()->from('table')->having("field", "in", array(2, 3, 4));
        $this->assertEquals('SELECT * FROM table HAVING field IN (?,?,?)', $this->query->getSql());
        $this->assertEquals(array(2, 3, 4), $this->query->getBinds());
    }

    public function testSelectHavingNot(){

        $this->query->select()->from('table')->having("field", "<>", "value");
        $this->assertEquals('SELECT * FROM table HAVING field <> ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingGreater(){

        $this->query->select()->from('table')->having("field", ">", "value");
        $this->assertEquals('SELECT * FROM table HAVING field > ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingGreaterEqual(){

        $this->query->select()->from('table')->having("field", ">=", "value");
        $this->assertEquals('SELECT * FROM table HAVING field >= ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingSmaller(){

        $this->query->select()->from('table')->having("field", "<", "value");
        $this->assertEquals('SELECT * FROM table HAVING field < ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingSmallerEqual(){

        $this->query->select()->from('table')->having("field", "<=", "value");
        $this->assertEquals('SELECT * FROM table HAVING field <= ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testSelectHavingAnd(){

        $this->query->select()->from('table')->having("field1", "value1")->and()->where("field2", "value2");
        $this->assertEquals('SELECT * FROM table HAVING field1 = ? AND field2 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());
    }

    public function testSelectHavingAndOpen(){

        $this->query->select()->from('table')->having("field1", "value1")->and()->open()->where("field2", "value2")->or()->where("field3", "value3")->close();
        $this->assertEquals('SELECT * FROM table HAVING field1 = ? AND ( field2 = ? OR field3 = ? )', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }

    public function testSelectHavingOr(){

        $this->query->select()->from('table')->having("field1", "value1")->or()->where("field2", "value2");
        $this->assertEquals('SELECT * FROM table HAVING field1 = ? OR field2 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());
    }

    public function testSelectHavingOrOpen(){

        $this->query->select()->from('table')->having("field1", "value1")->or()->open()->where("field2", "value2")->and()->where("field3", "value3")->close();
        $this->assertEquals('SELECT * FROM table HAVING field1 = ? OR ( field2 = ? AND field3 = ? )', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }
    public function testSelectHavingOpen(){

        $this->query->select()->from('table')
            ->having()->open()->where("field1", "value1")->and()->where("field2", "value2")->close()
            ->or()->where('field3', 'value3');
        $this->assertEquals('SELECT * FROM table HAVING ( field1 = ? AND field2 = ? ) OR field3 = ?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2', 'value3'), $this->query->getBinds());
    }

    /*
     * Update
     */

    public function testUpdateSimple(){

        $this->query->update('table')->set(array('field' => 'value'));
        $this->assertEquals('UPDATE table SET field=?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testUpdateMultiple(){

        $this->query->update('table')->set(array('field1' => 'value1', "field2" => 'value2'));
        $this->assertEquals('UPDATE table SET field1=?, field2=?', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());
    }

    /*
     * Delete
     */

    public function testDeleteSimple(){

        $this->query->delete()->from('table');
        $this->assertEquals('DELETE FROM table', $this->query->getSql());
    }

    /*
     * Insert
     */

    public function testInsertSimple(){

        $this->query->insert(array('field' => 'value'), 'table');
        $this->assertEquals('INSERT INTO table (field) VALUES (?)', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());

    }

    public function testInsertMultiple(){

        $this->query->insert(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('INSERT INTO table (field1,field2) VALUES (?,?)', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());

    }

    /*
     * Replace
     */

    public function testReplaceSimple(){

        $this->query->replace(array('field' => 'value'), 'table');
        $this->assertEquals('REPLACE INTO table (field) VALUES (?)', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());

    }

    public function testReplaceMultiple(){

        $this->query->replace(array('field1' => 'value1', 'field2' => 'value2'), 'table');
        $this->assertEquals('REPLACE INTO table (field1,field2) VALUES (?,?)', $this->query->getSql());
        $this->assertEquals(array('value1', 'value2'), $this->query->getBinds());

    }

    /*
    * Joins
    */

    public function testJoin(){

        $this->query->select()->from('table')->join('other')->on("field", "value")->where("field", "value2");
        $this->assertEquals('SELECT * FROM table JOIN other ON field = ? WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value', 'value2'), $this->query->getBinds());
    }

    public function testJoinLeft(){

        $this->query->select()->from('table')->left_join('other')->on("field", "value")->where("field", "value2");
        $this->assertEquals('SELECT * FROM table LEFT JOIN other ON field = ? WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value', 'value2'), $this->query->getBinds());
    }

    public function testJoinRight(){

        $this->query->select()->from('table')->right_join('other')->on("field", "value")->where("field", "value2");
        $this->assertEquals('SELECT * FROM table RIGHT JOIN other ON field = ? WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value', 'value2'), $this->query->getBinds());
    }

    public function testJoinFull(){

        $this->query->select()->from('table')->full_join('other')->on("field", "value")->where("field", "value2");
        $this->assertEquals('SELECT * FROM table FULL JOIN other ON field = ? WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value', 'value2'), $this->query->getBinds());
    }

    public function testJoinAdvanced(){

        $this->query->select()->from('table')
            ->left_join('other_a')->on("field", "value")
            ->right_join('other_b')->on("field", "value2")
            ->full_join('other_c')->on("field", "value3")
            ->where("field", "value4");
        $this->assertEquals('SELECT * FROM table LEFT JOIN other_a ON field = ? RIGHT JOIN other_b ON field = ? FULL JOIN other_c ON field = ? WHERE field = ?', $this->query->getSql());
        $this->assertEquals(array('value', 'value2', 'value3', 'value4'), $this->query->getBinds());
    }

    /**
     * Inline joins
     */
    public function testJoinInline(){

        $this->query->select()->from('table t', 'other o')->where("o.field", "value");
        $this->assertEquals('SELECT * FROM table t, other o WHERE o.field = ?', $this->query->getSql());
        $this->assertEquals(array('value'), $this->query->getBinds());
    }

    public function testJoinInlineAdvanced(){

        $this->query->select()->from('table t', 'other o')->where("o.field = t.field");
        $this->assertEquals('SELECT * FROM table t, other o WHERE o.field = t.field', $this->query->getSql());
        $this->assertEmpty($this->query->getBinds());
    }

    public function testJoinAllInOne(){

        $this->query->select()
            ->from('table t', 'other o')
                ->left_join('third th')->on('th.field = o.field')
            ->where("o.field = t.field")
                ->and()->where('t.thing', '>', 25);
        $this->assertEquals('SELECT * FROM table t, other o LEFT JOIN third th ON th.field = o.field WHERE o.field = t.field AND t.thing > ?', $this->query->getSql());
        $this->assertEquals(array(25), $this->query->getBinds());
    }

    /**
     * Group by
     */

    public function testSelectGroupBy(){

        $this->query->groupBy('field1', 'field2');
        $this->assertEquals('GROUP BY field1, field2', $this->query->getSql());
    }
}