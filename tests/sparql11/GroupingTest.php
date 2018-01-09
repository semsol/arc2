<?php

namespace Tests\sparql11;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class GroupingTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/grouping';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/grouping/manifest#';
    }

    /*
     * tests
     */

    public function test_group01()
    {
        $this->runTestFor('group01');
    }

    public function test_group02()
    {
        $this->runTestFor('group02');
    }

    public function test_group03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'group03');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'Query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_group04()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'group04');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'Query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_group05()
    {
        $this->runTestFor('group05');
    }

    public function test_group06()
    {
        $this->runTestFor('group06');
    }

    public function test_group07()
    {
        $this->runTestFor('group07');
    }
}
