<?php

namespace Tests\integration;

use Tests\ARC2_TestCase;

class ARC2_ClassTest extends ARC2_TestCase
{
    protected $dbConnection;

    public function setUp()
    {
        parent::setUp();

        $store = \ARC2::getStore($this->dbConfig);
        $this->dbConnection = $store->getDBCon();

        $this->fixture = new \ARC2_Class([], $this);
    }

    /*
     * Tests for queryDB
     */

    public function testQueryDB()
    {
        $result = $this->fixture->queryDB('SHOW TABLES', $this->dbConnection);
        $this->assertEquals(1, $result->field_count);
        $this->assertEquals(6, $result->num_rows);
    }

    public function testQueryDBInvalidQuery()
    {
        $result = $this->fixture->queryDB('invalid-query', $this->dbConnection);
        $this->assertFalse($result);
    }

    public function testQueryDBInvalidQueryWithLog()
    {
        $result = $this->fixture->queryDB('invalid-query', $this->dbConnection, true);
        $this->assertFalse($result);
        $this->assertEquals(
            [
                'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near \'invalid-query\' at line 1'
            ],
            $this->fixture->errors
        );
    }
}
