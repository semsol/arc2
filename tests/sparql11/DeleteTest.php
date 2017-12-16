<?php

namespace Tests\sparql11;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class DeleteTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/delete';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/delete/manifest#';
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

            :dawg-delete-01 a mf:UpdateEvaluationTest ;
                [...]
                mf:action [ ut:request <delete-01.ru> ;
                            ut:data <delete-pre-01.ttl>
                          ] ;
                mf:result [ ut:data <delete-post-01s.ttl>
                          ] .
         */
        $query = $this->store->query('
            PREFIX mf: <http://www.w3.org/2001/sw/DataAccess/tests/test-manifest#> .
            PREFIX ut: <http://www.w3.org/2009/sparql/tests/test-update#> .
            SELECT * FROM <'. $this->manifestGraphUri .'> WHERE {
                <'. $testUri .'> mf:action [ ut:request ?queryFile ] .
            }
        ');

        return file_get_contents($query['result']['rows'][0]['queryFile']);
    }

    /*
     * tests
     */

    public function test_dawg_delete_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-01');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-02');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-03');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_04()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-04');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_05()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-05');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_06()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-06');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_07()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-07');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-01');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-02');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-03');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_04()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-04');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_05()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-05');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_with_06()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-with-06');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-01');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_02a()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-02a');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-03');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_04()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-04');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_05()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-05');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }

    public function test_dawg_delete_using_06a()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'dawg-delete-using-06a');

        $this->markTestSkipped(
            'This kind of delete query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#delete-example'
            . PHP_EOL . 'Query: ' . $this->makeQueryA1Liner($query)
        );
    }
}
