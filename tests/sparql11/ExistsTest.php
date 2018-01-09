<?php

namespace Tests\sparql11;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class ExistsTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/exists';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/exists/manifest#';
    }

    /*
     * tests
     */

    public function test_exists01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'exists01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_exists02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'exists02');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_exists03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'exists03');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_exists04()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'exists04');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_exists05()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'exists05');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }
}
