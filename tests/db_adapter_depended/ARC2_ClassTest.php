<?php

namespace Tests\db_adapter_depended;

use Tests\ARC2_TestCase;

class ARC2_ClassTest extends ARC2_TestCase
{
    protected $dbConnection;
    protected $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = \ARC2::getStore($this->dbConfig);
        $this->store->createDBCon();
        $this->store->setup();
        $this->dbConnection = $this->store->getDBCon();

        $this->fixture = new \ARC2_Class($this->dbConfig, $this);

        if ('mysqli' !== $this->dbConfig['db_adapter']) {
            $this->markTestSkipped('Db adapter is not mysqli, therefore skip tests with queryDB.');
        }
    }

    /*
     * Tests for queryDB
     */

    public function testQueryDB()
    {
        $this->store->createDBCon();
        $this->store->setup();

        $result = $this->fixture->queryDB('SHOW TABLES', $this->dbConnection);
        $this->assertEquals(1, $result->field_count);
        $this->assertTrue(0 < $result->num_rows);
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

        if ('mysql' == $this->store->getDBSName()) {
            $dbsName = 'MySQL';
        } else {
            $dbsName = 'MariaDB';
        }

        $this->assertEquals(
            [
                'You have an error in your SQL syntax; check the manual that corresponds to your '
                .$dbsName.' server version for the right syntax to use near \'invalid-query\' at line 1'
            ],
            $this->fixture->errors
        );
    }
}
