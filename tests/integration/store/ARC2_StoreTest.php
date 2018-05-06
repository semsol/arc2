<?php

namespace Tests\integration\store;

use Tests\ARC2_TestCase;

class ARC2_StoreTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);
        $this->fixture->drop();
        $this->fixture->setup();
    }

    public function testSetup()
    {
        $this->fixture->reset();

        $this->fixture->setup();
    }

    /*
     * Tests for createDBCon
     */

    public function testCreateDBConDatabaseNotAvailable()
    {
        $this->fixture->queryDB('DROP DATABASE '.$this->fixture->a['db_name'], $this->fixture->getDBCon());
        $databases = $this->fixture->queryDB('SHOW DATABASES', $this->fixture->getDBCon());
        while($row = mysqli_fetch_array($databases)) {
            $this->assertFalse($this->fixture->a['db_name'] == $row[0]);
        }

        // create connection, which also creates the DB
        $this->fixture->createDBCon();

        $databases = $this->fixture->queryDB('SHOW DATABASES', $this->fixture->getDBCon());
        $foundDb = false;
        while($row = mysqli_fetch_array($databases)) {
            if ($this->fixture->a['db_name'] == $row[0]) {
                $foundDb = true;
            }
        }
        $this->assertTrue($foundDb);
    }

    /*
     * Tests for closeDBCon
     */

    public function testCloseDBCon()
    {
        $this->assertTrue(isset($this->fixture->a['db_con']));

        $this->fixture->closeDBCon();

        $this->assertFalse(isset($this->fixture->a['db_con']));
    }

    /*
     * Tests for delete
     */

    public function testDelete()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
            <http://s> <http://xmlns.com/foaf/0.1/name> "label1" .
        }');

        $res = $this->fixture->query('SELECT * WHERE {?s ?p ?o.}');
        $this->assertEquals(2, \count($res['result']['rows']));

        // remove graph
        $this->fixture->delete(false, 'http://example.com/');

        $res = $this->fixture->query('SELECT * WHERE {?s ?p ?o.}');
        $this->assertEquals(0, \count($res['result']['rows']));
    }

    /*
     * Tests for drop
     */

    public function testDrop()
    {
        // make sure all tables were created
        $this->fixture->setup();
        $tables = $this->fixture->getTables();
        $res = $this->fixture->queryDB('SHOW TABLES;', $this->fixture->getDBCon());
        $this->assertEquals(6, $res->num_rows);

        // remove all tables
        $this->fixture->drop();

        // check that all tables were removed
        $res = $this->fixture->queryDB('SHOW TABLES;', $this->fixture->getDBCon());
        $this->assertEquals(0, $res->num_rows);
    }

    /*
     * Tests for dump
     */

    public function testDump()
    {
        $this->markTestSkipped(
            'Can not test dump-method, because it causes the following error:'
            .PHP_EOL
            .'Cannot modify header information - headers already sent by (output started at ./vendor/phpunit/phpunit/src/Util/Printer.php:110)'
        );
    }

    /*
     * Tests for getDBVersion
     */

    // just check pattern
    public function testGetDBVersion()
    {
        $result = preg_match('/[0-9]{2}-[0-9]{2}-[0-9]{2}/', $this->fixture->getDBVersion(), $match);
        $this->assertEquals(1, $result);
    }

    /*
     * Tests for getSetting and setSetting
     */

    public function testGetAndSetSetting()
    {
        $this->assertEquals(0, $this->fixture->getSetting('foo'));

        $this->fixture->setSetting('foo', 'bar');

        $this->assertEquals('bar', $this->fixture->getSetting('foo'));
    }

    /*
     * Tests for getLabelProps
     */

    public function testGetLabelProps()
    {
        $this->assertEquals(
            [
                'http://www.w3.org/2000/01/rdf-schema#label',
                'http://xmlns.com/foaf/0.1/name',
                'http://purl.org/dc/elements/1.1/title',
                'http://purl.org/rss/1.0/title',
                'http://www.w3.org/2004/02/skos/core#prefLabel',
                'http://xmlns.com/foaf/0.1/nick',
            ],
            $this->fixture->getLabelProps()
        );
    }

    /*
     * Tests for getResourceLabel
     */

    public function testGetResourceLabel()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
            <http://s> <http://xmlns.com/foaf/0.1/name> "label1" .
        }');

        $res = $this->fixture->getResourceLabel('http://s');

        $this->assertEquals('label1', $res);
    }

    public function testGetResourceLabelNoData()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
        }');

        $res = $this->fixture->getResourceLabel('http://s');

        $this->assertEquals('s', $res);
    }

    /*
     * Tests for getResourcePredicates
     */

    public function testGetResourcePredicates()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
            <http://s> <http://p2> "bar" .
        }');

        $res = $this->fixture->getResourcePredicates('http://s');

        $this->assertEquals(
            [
                'http://p1' => [],
                'http://p2' => [],
            ],
            $res
        );
    }

    public function testGetResourcePredicatesMultipleGraphs()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
            <http://s> <http://p2> "bar" .
        }');

        $this->fixture->query('INSERT INTO <http://example.com/2> {
            <http://s> <http://p3> "baz" .
            <http://s> <http://p4> "bar" .
        }');

        $res = $this->fixture->getResourcePredicates('http://s');

        $this->assertEquals(
            [
                'http://p1' => [],
                'http://p2' => [],
                'http://p3' => [],
                'http://p4' => [],
            ],
            $res
        );
    }

    /*
     * Tests for getPredicateRange
     */

    public function testGetPredicateRange()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://p1> <http://www.w3.org/2000/01/rdf-schema#range> <http://foobar> .
        }');

        $res = $this->fixture->getPredicateRange('http://p1');

        $this->assertEquals('http://foobar', $res);
    }

    public function testGetPredicateRangeNotFound()
    {
        $res = $this->fixture->getPredicateRange('http://not-available');

        $this->assertEquals('', $res);
    }

    /*
     * Tests for getIDValue
     */

    public function testGetIDValue()
    {
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://p1> <http://www.w3.org/2000/01/rdf-schema#range> <http://foobar> .
        }');

        $res = $this->fixture->getIDValue(1);

        $this->assertEquals('http://example.com/', $res);
    }

    public function testGetIDValueNoData()
    {
        $res = $this->fixture->getIDValue(1);

        $this->assertEquals(0, $res);
    }

    /*
     * Tests for logQuery
     */

    public function testLogQuery()
    {
        $logFile = 'arc_query_log.txt';

        $this->assertFalse(file_exists($logFile));

        $this->fixture->logQuery('query1');

        $this->assertTrue(file_exists($logFile));
        unlink($logFile);
    }

    /*
     * Tests for reset
     */

    public function testResetKeepSettings()
    {
        $this->fixture->setSetting('foo', 'bar');
        $this->assertEquals(1, $this->fixture->hasSetting('foo'));

        $this->fixture->reset(1);

        $this->assertEquals(1, $this->fixture->hasSetting('foo'));
    }
}
