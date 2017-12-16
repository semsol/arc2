<?php

namespace Tests\sparql11;

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
        $this->markTestSkipped(
            'Test skipped, because of rounding bug in AVG function. See https://github.com/semsol/arc2/issues/99'
        );
    }

    public function test_agg_avg_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-avg-02');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_empty_group()
    {
        $this->runTestFor('agg-empty-group');
    }

    public function test_agg_err_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-err-01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_err_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-err-02');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_groupconcat_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-groupconcat-01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_groupconcat_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-groupconcat-02');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_groupconcat_03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-groupconcat-03');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_max_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-max-01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'Result is empty and misses entry:'
            . PHP_EOL
            . '<binding name="max">'
            . '<literal datatype="http://www.w3.org/2001/XMLSchema#double">3.0E4</literal>'
            . '</binding>'
            . PHP_EOL
            . 'for query: '.
            $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_max_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-max-01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'Result is missing multiple entries.'
            . PHP_EOL
            . 'for query: '.
            $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_min_01()
    {
        $this->runTestFor('agg-min-01');
    }

    public function test_agg_min_02()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-min-02');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'Result is missing multiple entries.'
            . PHP_EOL
            . 'for query: '.
            $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_sample_01()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg-sample-01');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg_sum_01()
    {
        $this->markTestSkipped(
            'Test skipped, because of rounding bug in SUM function. See https://github.com/semsol/arc2/issues/100'
        );
    }

    public function test_agg01()
    {
        $this->runTestFor('agg01');
    }

    public function test_agg02()
    {
        $this->runTestFor('agg02');
    }

    public function test_agg03()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg03');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg04()
    {
        $this->runTestFor('agg04');
    }

    public function test_agg05()
    {
        $this->runTestFor('agg05');
    }

    public function test_agg06()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg06');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg07()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg07');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_agg08()
    {
        $this->runTestFor('agg08');
    }

    public function test_agg08b()
    {
        // get failing query
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'agg08b');

        $this->markTestSkipped(
            'This kind of query is currently not supported. '
            . 'ARC2_Store::query returns 0 for query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
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
