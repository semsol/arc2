<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\AbstractAdapter;
use ARC2\Store\Adapter\AdapterFactory;
use Tests\ARC2_TestCase;

class AdapterFactoryTest extends ARC2_TestCase
{
    public function setUp(): void
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

    public function testGetInstanceForInvalidAdapterName()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unknown adapter name given. Currently supported are: mysqli, pdo');

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
