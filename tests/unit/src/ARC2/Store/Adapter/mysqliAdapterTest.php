<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\mysqliAdapter;

class mysqliAdapterTest extends AbstractAdapterTest
{
    protected function checkAdapterRequirements()
    {
        // stop, if mysqli is not available
        if (false == \extension_loaded('mysqli') || false == \function_exists('mysqli_connect')) {
            $this->markTestSkipped('Test skipped, because extension mysqli is not installed.');
        }
    }

    protected function getAdapterInstance($configuration)
    {
        return new mysqliAdapter($configuration);
    }

    public function testConnectUseGivenConnection()
    {
        $this->fixture->disconnect();

        // create connection outside of the instance
        $connection = mysqli_connect(
            $this->dbConfig['db_host'],
            $this->dbConfig['db_user'],
            $this->dbConfig['db_pwd'],
            $this->dbConfig['db_name']
        );

        $this->fixture = new mysqliAdapter();

        // use existing connection
        $this->fixture->connect($connection);

        // if not the same origin, the connection ID differs
        $this->assertEquals($this->fixture->getConnectionId(), mysqli_thread_id($connection));

        // simple test query to check that its working
        $result = $this->fixture->simpleQuery('SHOW TABLES');
        $this->assertTrue($result);
    }

    public function testEscape()
    {
        $this->assertEquals('\"hallo\"', $this->fixture->escape('"hallo"'));
    }

    public function testGetAdapterName()
    {
        $this->assertEquals('mysqli', $this->fixture->getAdapterName());
    }

    public function testGetConnection()
    {
        $this->assertTrue($this->fixture->getConnection() instanceof \mysqli);
    }

    /*
     * Tests for getErrorMessage and getErrorCode
     */

    public function testGetErrorMessageAndGetErrorCode()
    {
        // invalid query
        $result = $this->fixture->simpleQuery('SHOW TABLES of x');
        $this->assertFalse($result);
        $errorMsg = $this->fixture->getErrorMessage();
        $errorCode = $this->fixture->getErrorCode();

        $dbs = 'mariadb' == $this->fixture->getDBSName() ? 'MariaDB' : 'MySQL';

        $this->assertEquals(
            "You have an error in your SQL syntax; check the manual that corresponds to your $dbs server "
            ."version for the right syntax to use near 'of x' at line 1",
            $errorMsg
        );

        $this->assertTrue(0 < $errorCode);
    }

    public function testGetNumberOfRowsInvalidQuery()
    {
        // run with invalid query
        $this->assertEquals(0, $this->fixture->getNumberOfRows('SHOW TABLES of x'));
    }

    public function testMysqliQuery()
    {
        $res = $this->fixture->mysqliQuery('SHOW TABLES');
        $this->assertTrue($res instanceof \mysqli_result);
    }

    public function testQueryInvalid()
    {
        // invalid query
        $this->assertFalse($this->fixture->simpleQuery('invalid query'));
    }
}
