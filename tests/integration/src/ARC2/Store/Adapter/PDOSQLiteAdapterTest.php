<?php

namespace Tests\integration\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\PDOSQLiteAdapter;

class PDOSQLiteAdapterTest extends AbstractAdapterTest
{
    protected function checkAdapterRequirements()
    {
        // stop, if pdo_mysql is not available
        if (false == \extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Test skipped, because extension pdo_mysql is not installed.');
        }
    }

    /**
     * Forces SQLite in-memory.
     */
    protected function getAdapterInstance($configuration)
    {
        // $configuration is being ignored for now. therefore no tests with
        // SQLite files, only :memory:.

        return new PDOSQLiteAdapter([
            'db_adapter' => 'pdo',
            'db_pdo_protocol' => 'sqlite',
        ]);
    }

    public function testConnectCreateNewConnection()
    {
        $this->fixture->disconnect();

        // do explicit reconnect
        $this->fixture = $this->getAdapterInstance($this->dbConfig);
        $this->fixture->connect();

        $this->fixture->exec('CREATE TABLE test (id INTEGER)');
        $this->assertEquals([], $this->fixture->fetchList('SELECT * FROM test;'));
    }

    public function testConnectUseGivenConnection()
    {
        $this->fixture->disconnect();
        $connection = new \PDO('sqlite::memory:');

        $connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(\PDO::ERRMODE_EXCEPTION, true);
        $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);

        $this->fixture = $this->getAdapterInstance($this->dbConfig);

        // use existing connection
        $this->fixture->connect($connection);

        /*
         * simple test query to check that its working
         */
        $this->fixture->simpleQuery('CREATE TABLE MyGuests (id INTEGER PRIMARY KEY AUTOINCREMENT)');

        $this->assertEquals(1, \count($this->fixture->getAllTables()));
    }

    public function testGetDBSNameNoConnection()
    {
        // disconnect current connection
        $this->fixture->disconnect();

        // create new instance, but dont connect
        $db = $this->getAdapterInstance($this->dbConfig);

        $this->assertEquals('sqlite', $db->getDBSName());
    }

    public function testEscape()
    {
        $this->assertEquals('"hallo"', $this->fixture->escape('"hallo"'));
    }

    public function testExec()
    {
        $this->fixture->exec('CREATE TABLE users (id INTEGER, name TEXT NOT NULL)');
        $this->fixture->exec('INSERT INTO users (id, name) VALUES (1, "foobar");');
        $this->fixture->exec('INSERT INTO users (id, name) VALUES (2, "foobar2");');

        $this->assertEquals(2, $this->fixture->exec('DELETE FROM users;'));
    }

    public function testFetchList()
    {
        // valid query
        $sql = 'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL)';
        $this->fixture->exec($sql);
        $this->assertEquals([], $this->fixture->fetchList('SELECT * FROM users'));

        // add data
        $this->fixture->exec('INSERT INTO users (id, name) VALUES (1, "foobar");');
        $this->assertEquals(
            [
                [
                    'id' => 1,
                    'name' => 'foobar',
                ],
            ],
            $this->fixture->fetchList('SELECT * FROM users')
        );
    }

    public function testFetchRow()
    {
        // valid query
        $this->fixture->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $this->assertFalse($this->fixture->fetchRow('SELECT * FROM users'));

        // add data
        $this->fixture->exec('INSERT INTO users (id, name) VALUES (1, "foobar");');
        $this->assertEquals(
            [
                'id' => 1,
                'name' => 'foobar',
            ],
            $this->fixture->fetchRow('SELECT * FROM users WHERE id = 1;')
        );
    }

    public function testGetAdapterName()
    {
        $this->assertEquals('pdo', $this->fixture->getAdapterName());
    }

    public function testGetCollation()
    {
        $this->markTestIncomplete('Implement getCollation for PDOSQLiteAdapter.');
    }

    public function testGetConnection()
    {
        $this->assertTrue($this->fixture->getConnection() instanceof \PDO);
    }

    public function testGetNumberOfRows()
    {
        // create test table
        $this->fixture->exec('CREATE TABLE pet (name TEXT)');
        $this->fixture->exec('INSERT INTO pet VALUES ("cat")');
        $this->fixture->exec('INSERT INTO pet VALUES ("dog")');

        $this->assertEquals(2, $this->fixture->getNumberOfRows('SELECT * FROM pet;'));
    }

    public function testGetNumberOfRowsInvalidQuery()
    {
        $this->expectException('Exception');

        $this->fixture->getNumberOfRows('SHOW TABLES of x');
    }

    public function testGetServerVersion()
    {
        // check server version
        $this->assertEquals(
            1,
            preg_match('/[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}/',
            'Found: '.$this->fixture->getServerVersion())
        );
    }

    public function testQuery()
    {
        // valid query
        $sql = 'CREATE TABLE MyGuests (id INTEGER PRIMARY KEY AUTOINCREMENT)';
        $this->fixture->exec($sql);

        $foundTable = false;
        foreach ($this->fixture->getAllTables() as $table) {
            if ('MyGuests' == $table) {
                $foundTable = true;
                break;
            }
        }
        $this->assertTrue($foundTable, 'Expected table not found.');
    }

    public function testQueryInvalid()
    {
        $this->expectException('Exception');

        // invalid query
        $this->assertFalse($this->fixture->simpleQuery('invalid query'));
    }

    public function testSimpleQueryNoConnection()
    {
        // test, that it creates a connection itself, when calling exec
        $this->fixture->disconnect();

        $db = $this->getAdapterInstance(['db_adapter' => 'pdo', 'db_pdo_protocol' => 'sqlite']);
        $sql = 'CREATE TABLE MyGuests (id INTEGER PRIMARY KEY AUTOINCREMENT)';
        $this->assertEquals(0, $db->exec($sql));
    }
}
