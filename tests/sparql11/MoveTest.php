<?php

namespace Tests\sparql11;

/**
 * Runs W3C tests from https://www.w3.org/2009/sparql/docs/tests/
 *
 * Version: 2012-10-23 20:52 (sparql11-test-suite-20121023.tar.gz)
 *
 * Tests are located in the w3c-tests folder.
 */
class MoveTest extends ComplianceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->w3cTestsFolderPath = __DIR__.'/w3c-tests/move';
        $this->testPref = 'http://www.w3.org/2009/sparql/docs/tests/data-sparql11/move/manifest#';
    }

    /*
     * tests
     */

    public function test_move01()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move02()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move03()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move04()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move05()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move06()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }

    public function test_move07()
    {
        $this->markTestSkipped(
            'MOVE query is currently not supported. '
            . 'https://github.com/semsol/arc2/wiki/SPARQL-#sparql-grammar-changes-and-additions'
        );
    }
}
