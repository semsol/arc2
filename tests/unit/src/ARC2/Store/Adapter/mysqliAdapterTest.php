<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\mysqliAdapter;
use Tests\ARC2_TestCase;

class mysqliAdapterTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // stop, if mysqli is not available
        if (false == \extension_loaded('mysqli') || false == \function_exists('mysqli_connect')) {
            $this->markTestSkipped('Test skipped, because extension mysqli is not installed.');
        }

        $this->fixture = new mysqliAdapter($this->dbConfig);
        $result = $this->fixture->connect();

        // remove all tables
        $tables = $this->fixture->fetchList('SHOW TABLES');
        foreach($tables as $table) {
            $this->fixture->simpleQuery('DROP TABLE '. $table['Tables_in_'.$this->dbConfig['db_name']]);
        }
    }

    public function tearDown()
    {
        $this->fixture->disconnect();
    }

    /*
     * Tests for connect
     */

    public function testConnectCreateNewConnection()
    {
        $this->fixture->disconnect();

        // do explicit reconnect
        $this->fixture = new mysqliAdapter($this->dbConfig);
        $this->fixture->connect();

        $result = $this->fixture->simpleQuery('SHOW TABLES');
        $this->assertTrue($result);
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

    /*
     * Tests for getDBSName
     */

    public function testGetDBSName()
    {
        $this->assertTrue(in_array($this->fixture->getDBSName(), array('mariadb', 'mysql')));
    }

    public function testGetDBSNameNoConnection()
    {
        $this->fixture->disconnect();

        // it will reconnect
        $this->assertEquals('mariadb', $this->fixture->getDBSName());
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

        $this->assertEquals(1064, $errorCode);
    }

    /*
     * Tests for getNumberOfRows
     */

    public function testGetNumberOfRows()
    {
        // create test table
        $this->fixture->simpleQuery('
            CREATE TABLE pet (name VARCHAR(20));
        ');

        $this->assertEquals(1, $this->fixture->getNumberOfRows('SHOW TABLES'));
    }

    public function testGetNumberOfRowsInvalidQuery()
    {
        // run with invalid query
        $this->assertEquals(0, $this->fixture->getNumberOfRows('SHOW TABLES of x'));
    }

    /*
     * Tests for query
     */

    public function testQuery()
    {
        // valid query
        $this->assertTrue($this->fixture->simpleQuery('SHOW TABLES'));

        // invalid query
        $this->assertFalse($this->fixture->simpleQuery('invalid query'));
    }
}
