<?php

namespace Tests\sparql11;

/**
 * Runs tests which are based on W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class DropTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/drop';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/drop/manifest#';
    }

    /**
     * @param string $graphUri
     * @todo Check that $graphUri is a valid URI
     * @todo port this functionality to ARC2_Store->query
     */
    protected function createGraph($graphUri)
    {
        // table names
        $g2t = 'arc_g2t';
        $id2val = 'arc_id2val';

        /*
         * for id2val table
         */
        $query = 'INSERT INTO '. $id2val .' (val) VALUES("'. $graphUri .'")';
        $this->store->queryDB($query, $this->store->getDBCon());
        $usedId = $this->store->getDBCon()->insert_id;

        /*
         * for g2t table
         */
        $newIdg2t = 1 + $this->getRowCount($g2t);
        $query = 'INSERT INTO '. $g2t .' (t, g) VALUES('. $newIdg2t .', '. $usedId .')';
        $this->store->queryDB($query, $this->store->getDBCon());
        $usedId = $this->store->getDBCon()->insert_id;
    }

    /**
     * Helper function to get test query for a given test.
     *
     * @param string $testUri
     * @return string Query to test.
     */
    protected function getTestQuery($testUri)
    {
        /*
            example:

            :group1 mf:action [
                qt:query  <group01.rq>
            ]
         */
        $query = $this->store->query('
            PREFIX mf: <http://www.w3.org/2001/sw/DataAccess/tests/test-manifest#> .
            PREFIX ut: <http://www.w3.org/2009/sparql/tests/test-update#> .
            SELECT * FROM <'. $this->manifestGraphUri .'> WHERE {
                <'. $testUri .'> mf:action [ ut:request ?queryFile ] .
            }
        ');

        return $query['result']['rows'][0]['queryFile'];
    }

    /*
     * tests
     */

    public function test_dawg_drop_all_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-drop-all-01');

        $this->markTestSkipped(
            'This kind of drop query is currently not supported. '
            . 'See https://github.com/semsol/arc2/wiki/SPARQL-'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_drop_default_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-drop-default-01');

        $this->markTestSkipped(
            'This kind of drop query is currently not supported. '
            . 'See https://github.com/semsol/arc2/wiki/SPARQL-'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_drop_graph_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-drop-graph-01');

        $this->markTestSkipped(
            'This kind of drop query is currently not supported. '
            . 'See https://github.com/semsol/arc2/wiki/SPARQL-'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_drop_named_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-drop-named-01');

        $this->markTestSkipped(
            'This kind of drop query is currently not supported. '
            . 'See https://github.com/semsol/arc2/wiki/SPARQL-'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    // this test is not part of the W3C test collection
    // it tests DELETE FROM <...> command which is the ARC2 equivalent to DROP GRAPH <...>
    public function test_delete_graph()
    {
        $graphUri = 'http://example.org/g1';

        // create graph
        $this->createGraph($graphUri);

        // load test data into graph
        $parser = \ARC2::getTurtleParser();
        $parser->parse($this->w3cTestsFolderPath .'/drop-g1.ttl');
        $this->store->insert($parser->getSimpleIndex(), $graphUri);

        // check if graph really contains data
        $res = $this->store->query('SELECT * FROM <'. $graphUri .'> WHERE {?s ?p ?o.}');
        $this->assertTrue(0 < count($res['result']['rows']), 'No test data in graph found.');

        // run test query
        $res = $this->store->query('DELETE FROM <'. $graphUri .'>');

        // check if test data are still available
        $res = $this->store->query('SELECT * FROM <'. $graphUri .'> WHERE {?s ?p ?o.}');
        $this->assertTrue(0 == count($res['result']['rows']));
    }
}
