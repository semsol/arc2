<?php

namespace Tests\integration\store;

use Tests\ARC2_TestCase;

class ARC2_StoreTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);

        // remove all tables
        $res = $this->fixture->queryDB('SHOW TABLES', $this->fixture->getDBCon());
        while($row = mysqli_fetch_array($res)) {
            $this->fixture->queryDB('DROP TABLE '. $row[0], $this->fixture->getDBCon());
        }

        // fresh setup of ARC2
        $this->fixture->setup();
    }

    /**
     * Returns a list of all available graph URIs of the store. It can also respect access control,
     * to only returned available graphs in the current context. But that depends on the implementation
     * and can differ.
     *
     * @return array simple array of key-value-pairs, which consists of graph URIs as key and NamedNode
     *               instance as value
     */
    protected function getGraphs($prefix = 'arc')
    {
        $g2t = $prefix.'_g2t';
        $id2val = $prefix.'_id2val';

        // collects all values which have an ID (column g) in the g2t table.
        $query = 'SELECT id2val.val AS graphUri
            FROM '.$g2t.' g2t
            LEFT JOIN '.$id2val.' id2val ON g2t.g = id2val.id
            GROUP BY g';

        // send SQL query
        $result = $this->fixture->queryDB($query, $this->fixture->getDBCon());
        $graphs = [];

        // collect graph URI's
        while ($row = $result->fetch_assoc()) {
            $graphs[] = $row['graphUri'];
        }

        return $graphs;
    }

    public function testSetup()
    {
        $this->fixture->reset();

        $this->fixture->setup();
    }

    /*
     * Tests for changeNamespaceURI
     */

    public function testChangeNamespaceURIEmptyStore()
    {
        $res = $this->fixture->changeNamespaceURI(
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'urn:rdf'
        );

        $this->assertEquals(
            [
                'id_replacements' => 0,
                'triple_updates' => 0,
            ],
            $res
        );
    }

    public function testChangeNamespaceURIFilledStore()
    {
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://pref/s> <http://pref/p1> "baz" .
        }');

        $res = $this->fixture->changeNamespaceURI(
            'http://pref/',
            'urn:rdf'
        );

        $this->assertEquals(
            [
                'id_replacements' => 2,
                'triple_updates' => 0,
            ],
            $res
        );
    }

    /*
     * Tests for countDBProcesses
     */

    public function testCountDBProcesses()
    {
        $this->assertTrue(0 < $this->fixture->countDBProcesses());
    }

    /*
     * Tests for createBackup
     */

    public function testCreateBackup()
    {
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
        }');

        $this->fixture->createBackup('/tmp/backup.txt');

        $expectedXML = <<<XML
<?xml version="1.0"?>
<sparql xmlns="http://www.w3.org/2005/sparql-results#">
  <head>
    <variable name="s"/>
    <variable name="p"/>
    <variable name="o"/>
    <variable name="g"/>
  </head>
  <results>
    <result>
      <binding name="s">
        <uri>http://s</uri>
      </binding>
      <binding name="p">
        <uri>http://p1</uri>
      </binding>
      <binding name="o">
        <literal>baz</literal>
      </binding>
      <binding name="g">
        <uri>http://example.com/</uri>
      </binding>
    </result>
  </results>
</sparql>

XML;
        $this->assertEquals(file_get_contents('/tmp/backup.txt'), $expectedXML);
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
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
        }');

        // fixed dump call using error_reporting to avoid
        // Cannot modify header information - headers already sent by (output started at
        // ./vendor/phpunit/phpunit/src/Util/Printer.php:110)
        // thanks to https://github.com/sebastianbergmann/phpunit/issues/720#issuecomment-364024753
        error_reporting(0);
        ob_start();
        $this->fixture->dump();
        $dumpContent = ob_get_clean();
        error_reporting(E_ALL);

        $expectedXML = <<<XML
<?xml version="1.0"?>
<sparql xmlns="http://www.w3.org/2005/sparql-results#">
  <head>
    <variable name="s"/>
    <variable name="p"/>
    <variable name="o"/>
    <variable name="g"/>
  </head>
  <results>
    <result>
      <binding name="s">
        <uri>http://s</uri>
      </binding>
      <binding name="p">
        <uri>http://p1</uri>
      </binding>
      <binding name="o">
        <literal>baz</literal>
      </binding>
      <binding name="g">
        <uri>http://example.com/</uri>
      </binding>
    </result>
  </results>
</sparql>

XML;
        $this->assertEquals($expectedXML, $dumpContent);
    }

    /*
     * Tests for enableFulltextSearch
     */

    public function testEnableFulltextSearch()
    {
        $res1 = $this->fixture->enableFulltextSearch();
        $res2 = $this->fixture->disableFulltextSearch();

        $this->assertNull($res1);
        $this->assertEquals(1, $res2);

        $this->assertEquals(0, $this->fixture->a['db_con']->errno);
        $this->assertEquals('', $this->fixture->a['db_con']->error);
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

    public function testGetAndSetSettingExistingSetting()
    {
        $this->assertEquals(0, $this->fixture->getSetting('foo'));

        $this->fixture->setSetting('foo', 'bar');
        $this->fixture->setSetting('foo', 'bar2'); // overrides existing setting

        $this->assertEquals('bar2', $this->fixture->getSetting('foo'));
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
     * Tests for renameTo
     */

    public function testRenameTo()
    {
        /*
         * remove all tables
         */
        $res = $this->fixture->queryDB('SHOW TABLES', $this->fixture->getDBCon());
        while($row = mysqli_fetch_array($res)) {
            $this->fixture->queryDB('DROP TABLE '. $row[0], $this->fixture->getDBCon());
        }

        /*
         * create fresh store and check tables
         */
        $this->fixture->setup();
        $res = $this->fixture->queryDB('SHOW TABLES', $this->fixture->getDBCon());
        while($row = mysqli_fetch_array($res)) {
            $this->assertEquals(substr($row[0], 0, 4), 'arc_');
        }

        /*
         * rename store
         */
        $prefix = 'renamed';
        $this->fixture->renameTo($prefix);

        /*
         * check for new prefixes
         */
        $res = $this->fixture->queryDB('SHOW TABLES', $this->fixture->getDBCon());
        while($row = mysqli_fetch_array($res)) {
            $this->assertEquals(substr($row[0], 0, 7), $prefix);
        }
    }

    /*
     * Tests for replace
     */

    public function testReplace()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://original/> {
            <http://s> <http://p1> "baz" .
            <http://s> <http://xmlns.com/foaf/0.1/name> "label1" .
        }');

        $res = $this->fixture->query('SELECT * WHERE {?s ?p ?o.}');
        $this->assertEquals(2, \count($res['result']['rows']));

        $this->assertEquals(
            [
                'http://original/'
            ],
            $this->getGraphs()
        );

        // replace graph
        $returnVal = $this->fixture->replace(false, 'http://original/', 'http://replacement/');

        // check triples
        $res = $this->fixture->query('SELECT * FROM <http://original/> WHERE {?s ?p ?o.}');
        $this->assertEquals(0, \count($res['result']['rows']));

        // get available graphs
        $this->assertEquals(0, \count($this->getGraphs()));

        $res = $this->fixture->query('SELECT * FROM <http://replacement/> WHERE {?s ?p ?o.}');
        // TODO this does not makes sense, why are there no triples?
        $this->assertEquals(0, \count($res['result']['rows']));

        $res = $this->fixture->query('SELECT * WHERE {?s ?p ?o.}');
        // TODO this does not makes sense, why are there no triples?
        $this->assertEquals(0, \count($res['result']['rows']));

        // check return value
        $this->assertEquals(
            [
                [
                    't_count' => 2,
                    'delete_time' => $returnVal[0]['delete_time'],
                    'index_update_time' => $returnVal[0]['index_update_time']
                ],
                false
            ],
            $returnVal
        );
    }

    /*
     * Tests for replicateTo
     */

    public function testReplicateTo()
    {
        if ('05-06' == substr($this->fixture->getDBVersion(), 0, 5)) {
            $this->markTestSkipped('With MySQL 5.6 ARC2_Store::replicateTo does not work. Tables keep their names.');
        }

        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "2009-05-28T18:03:38+09:00" .
            <http://s> <http://p1> "2009-05-28T18:03:38+09:00GMT" .
            <http://s> <http://p1> "21 August 2007" .
        }');

        // replicate
        $this->fixture->replicateTo('replicate');

        /*
         * check for new prefixes
         */
        $res = $this->fixture->queryDB('SHOW TABLES', $this->fixture->getDBCon());
        $foundArcPrefix = $foundReplicatePrefix = false;
        while($row = mysqli_fetch_array($res)) {
            if ('arc_' == substr($row[0], 0, 4)) {
                $foundArcPrefix = true;
            } elseif ('replicate' == substr($row[0], 0, 9)) {
                $foundReplicatePrefix = true;
            }
        }

        $this->assertTrue($foundArcPrefix);
        $this->assertTrue($foundReplicatePrefix);
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
