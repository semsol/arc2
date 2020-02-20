<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use Tests\ARC2_TestCase;

abstract class AbstractAdapterTest extends ARC2_TestCase
{
    abstract protected function checkAdapterRequirements();
    abstract protected function getAdapterInstance($config);
    abstract public function testConnectUseGivenConnection();
    abstract public function testEscape();
    abstract public function testGetAdapterName();
    abstract public function testGetConnection();
    abstract public function testGetNumberOfRowsInvalidQuery();

    public function setUp(): void
    {
        parent::setUp();

        $this->checkAdapterRequirements();

        $this->fixture = $this->getAdapterInstance($this->dbConfig);
        $result = $this->fixture->connect();

        // remove all tables
        $tables = $this->fixture->fetchList('SHOW TABLES');
        foreach($tables as $table) {
            $this->fixture->simpleQuery('DROP TABLE '. $table['Tables_in_'.$this->dbConfig['db_name']]);
        }
    }

    public function tearDown(): void
    {
        if (null !== $this->fixture) {
            $this->fixture->disconnect();
        }
    }

    protected function dropAllTables()
    {
        // remove all tables
        $tables = $this->fixture->fetchList('SHOW TABLES');
        foreach($tables as $table) {
            $this->fixture->simpleQuery('DROP TABLE '. $table['Tables_in_'.$this->dbConfig['db_name']]);
        }
    }

    /*
     * Tests for connect
     */

    public function testConnectCreateNewConnection()
    {
        $this->fixture->disconnect();

        // do explicit reconnect
        $this->fixture = $this->getAdapterInstance($this->dbConfig);
        $this->fixture->connect();

        $tables = $this->fixture->fetchList('SHOW TABLES');
        $this->assertTrue(is_array($tables));
    }

    /*
     * Tests for exec
     */

    public function testExec()
    {
        $this->fixture->simpleQuery('CREATE TABLE users (id INT(6), name VARCHAR(30) NOT NULL)');
        $this->fixture->simpleQuery('INSERT INTO users (id, name) VALUE (1, "foobar");');
        $this->fixture->simpleQuery('INSERT INTO users (id, name) VALUE (2, "foobar2");');

        $this->assertEquals(2, $this->fixture->exec('DELETE FROM users;'));
    }

    /*
     * Tests for fetchRow
     */

    public function testFetchRow()
    {
        // valid query
        $sql = 'CREATE TABLE users (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(30) NOT NULL
        )';
        $this->fixture->simpleQuery($sql);
        $this->assertEquals(null, $this->fixture->fetchRow('SELECT * FROM users'));

        // add data
        $this->fixture->simpleQuery('INSERT INTO users (id, name) VALUE (1, "foobar");');
        $this->assertEquals(
            [
                'id' => 1,
                'name' => 'foobar',
            ],
            $this->fixture->fetchRow('SELECT * FROM users WHERE id = 1;')
        );
    }

    /*
     * Tests for fetchList
     */

    public function testFetchList()
    {
        // valid query
        $sql = 'CREATE TABLE users (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(30) NOT NULL
        )';
        $this->fixture->simpleQuery($sql);
        $this->assertEquals([], $this->fixture->fetchList('SELECT * FROM users'));

        // add data
        $this->fixture->simpleQuery('INSERT INTO users (id, name) VALUE (1, "foobar");');
        $this->assertEquals(
            [
                [
                    'id' => 1,
                    'name' => 'foobar',
                ]
            ],
            $this->fixture->fetchList('SELECT * FROM users')
        );
    }

    /*
     * Tests for getCollation
     */

    public function testGetCollation()
    {
        // g2t table
        if (isset($this->dbConfig['db_table_prefix'])) {
            $table = $this->dbConfig['db_table_prefix'] . '_';
        } else {
            $table = '';
        }
        if (isset($this->dbConfig['store_name'])) {
            $table .= $this->dbConfig['store_name'] . '_';
        }
        $table .= 'setting';

        // create setting table which is used to determine collation
        $sql = 'CREATE TABLE '.$table.' (
          k char(32) NOT NULL,
          val text NOT NULL,
          UNIQUE KEY (k)
        ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci DELAY_KEY_WRITE = 1';
        $this->fixture->simpleQuery($sql);

        $this->assertEquals('utf8_unicode_ci', $this->fixture->getCollation());
    }

    // setting table not there
    public function testGetCollationNoReferenceTable()
    {
        $this->assertEquals('', $this->fixture->getCollation());
    }

    /*
     * Tests for getDBSName
     */

    public function testGetDBSName()
    {
        // connect and check
        $this->fixture->connect();
        $this->assertTrue(in_array($this->fixture->getDBSName(), array('mariadb', 'mysql')));
    }

    public function testGetDBSNameNoConnection()
    {
        // disconnect current connection
        $this->fixture->disconnect();

        // create new instance, but dont connect
        $db = $this->getAdapterInstance($this->dbConfig);

        $this->assertNull($db->getDBSName());
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

    /*
     * Tests for query
     */

    public function testQuery()
    {
        // valid query
        $sql = 'CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY)';
        $this->fixture->simpleQuery($sql);

        $foundTable = false;
        foreach ($this->fixture->fetchList('SHOW TABLES') as $entry) {
            if ('MyGuests' == $entry['Tables_in_'.$this->dbConfig['db_name']]) {
                $foundTable = true;
                break;
            }
        }
        $this->assertTrue($foundTable, 'Expected table not found.');
    }

    /*
     * Tests for getServerVersion
     */

    public function testGetServerVersion()
    {
        // check that server version looks like 05-00-05
        $this->assertEquals(1, preg_match('/\d\d-\d\d-\d\d/', $this->fixture->getServerVersion()));
    }

    /*
     * Tests for getStoreName
     */

    public function testGetStoreName()
    {
        $this->assertEquals($this->dbConfig['store_name'], $this->fixture->getStoreName());
    }

    public function testGetStoreNameNotDefined()
    {
        $this->fixture->disconnect();

        $copyOfDbConfig = $this->dbConfig;
        unset($copyOfDbConfig['store_name']);

        $db = $this->getAdapterInstance($copyOfDbConfig);

        $this->assertEquals('arc', $db->getStoreName());
    }

    /*
     * Tests for simpleQuery
     */

    public function testSimpleQueryNoConnection()
    {
        // test, that it creates a connection itself, when calling simpleQuery
        $this->fixture->disconnect();

        $db = $this->getAdapterInstance($this->dbConfig);
        $sql = 'CREATE TABLE MyGuests (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY)';
        $this->assertTrue($db->simpleQuery($sql));
    }
}
