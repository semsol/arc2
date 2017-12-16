<?php

namespace Tests\sparql11;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class SyntaxUpdate1Test extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/syntax-update-1';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/syntax-update-1/manifest#';
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

            :test_1 rdf:type   mf:PositiveUpdateSyntaxTest11 ;
               dawgt:approval dawgt:Approved ;
               dawgt:approvedBy <http://www.w3.org/2009/sparql/meeting/2011-04-05#resolution_2> ;
               mf:name    "syntax-update-01.ru" ;
               mf:action  <syntax-update-01.ru> ;.
         */
        $query = $this->store->query('
            PREFIX mf: <http://www.w3.org/2001/sw/DataAccess/tests/test-manifest#> .
            SELECT * FROM <'. $this->manifestGraphUri .'> WHERE {
                <'. $testUri .'> mf:action ?queryFile .
            }
        ');

        return file_get_contents($query['result']['rows'][0]['queryFile']);
    }

    /*
     * tests
     */

    public function test_test_1()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_1');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertTrue(is_array($result) && isset($result['query_type']));
    }

    public function test_test_2()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_2');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertTrue(is_array($result) && isset($result['query_type']));
    }

    public function test_test_3()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_3');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_4()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_4');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_5()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_5');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_6()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_6');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_7()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_7');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_8()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_8');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_9()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_9');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_10()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_10');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_11()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_11');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_12()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_12');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_13()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_13');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_14()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_14');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_15()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_15');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_16()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_16');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_17()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_17');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_18()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_18');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_19()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_19');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_20()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_20');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_21()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_21');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_22()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_22');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_23()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_23');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_24()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_24');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_25()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_25');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_26()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_26');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_27()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_27');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_28()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_28');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_29()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_29');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_30()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_30');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_31()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_31');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_32()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_32');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_33()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_33');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_34()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_34');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_35()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_35');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_36()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_36');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_37()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_37');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_38()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_38');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_39()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_39');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_40()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_40');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_41()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_41');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_42()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_42');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_43()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_43');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_44()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_44');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_45()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_45');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_46()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_46');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_47()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_47');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_48()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_48');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_49()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_49');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_50()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_50');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_51()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_51');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped(
            'Query has to fail, but ARC2 returns an array as if query is considered valid. Query: '. PHP_EOL . $this->makeQueryA1Liner($query)
        );
    }

    public function test_test_52()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_52');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }

    public function test_test_53()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_53');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->markTestSkipped('This kind of query is currently not supported. Query: '. PHP_EOL . $this->makeQueryA1Liner($query));
    }

    public function test_test_54()
    {
        $this->loadManifestFileIntoStore($this->w3cTestsFolderPath);
        $query = $this->getTestQuery($this->testPref . 'test_54');

        // fire query
        $result = $this->store->query($query);

        // check result
        $this->assertEquals(0, $result);
    }
}
