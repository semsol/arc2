<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\AbstractAdapter;
use ARC2\Store\Adapter\AdapterFactory;
use Tests\ARC2_TestCase;

class AdapterFactoryTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new AdapterFactory();
    }

    /*
     * Tests for getInstanceFor
     */

    public function testGetInstanceFor()
    {
        $this->assertTrue($this->fixture->getInstanceFor('mysqli') instanceof AbstractAdapter);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown adapter name given. Currently supported are: mysqli, pdo
     */
    public function testGetInstanceForInvalidAdapterName()
    {
        $this->fixture->getInstanceFor('invalid');
    }

    /*
     * Tests for getSupportedAdapters
     */

    public function testGetSupportedAdapters()
    {
        $this->assertEquals(array('mysqli', 'pdo'), $this->fixture->getSupportedAdapters());
    }
}
