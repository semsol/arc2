<?php

namespace Tests\db_adapter_depended\sparql_1_1_tests;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class AggregatesTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/aggregates';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/aggregates/manifest#';
    }

    /*
     * tests
     */

    public function test_agg_avg_01()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);

        $testname = 'agg-avg-01';

        // get test data
        $data = $this->getTestData($this->testPref . $testname);

        // load test data into graph
        $this->store->insert($data, $this->dataGraphUri);

        // get query to test
        $testQuery = $this->getTestQuery($this->testPref . $testname);

        // get expected result
        $expectedResult = $this->getExpectedResult($this->testPref . $testname);

        // get actual result for given test query
        $actualResult = $this->store->query($testQuery);
        $actualResultAsXml = $this->getXmlVersionOfResult($actualResult);

        $this->assertEquals(
            '2',
            (string) $actualResultAsXml->results->result->binding->literal[0]
        );

        // remember current behavior, but skip test anyway to show developer here is still a problem.
        $this->markTestSkipped(
            'Rounding bug in AVG function. See https://github.com/semsol/arc2/issues/99'
        );
    }

    public function test_agg_empty_group()
    {
        $this->runTestFor('agg-empty-group');
    }

    public function test_agg_min_01()
    {
        $this->runTestFor('agg-min-01');
    }

    public function test_agg01()
    {
        $this->runTestFor('agg01');
    }

    public function test_agg02()
    {
        $this->runTestFor('agg02');
    }

    public function test_agg04()
    {
        $this->runTestFor('agg04');
    }

    public function test_agg05()
    {
        $this->runTestFor('agg05');
    }

    public function test_agg08()
    {
        $this->runTestFor('agg08');
    }

    public function test_agg09()
    {
        $this->runTestFor('agg09');
    }

    public function test_agg10()
    {
        $this->runTestFor('agg10');
    }

    public function test_agg11()
    {
        $this->runTestFor('agg11');
    }

    public function test_agg12()
    {
        $this->runTestFor('agg12');
    }
}
