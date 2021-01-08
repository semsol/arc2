<?php

namespace Tests\integration\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\AbstractAdapter;
use ARC2\Store\Adapter\AdapterFactory;
use Exception;
use Tests\ARC2_TestCase;

class AdapterFactoryTest extends ARC2_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new AdapterFactory();
    }

    /*
     * Tests for getInstanceFor
     */

    public function testGetInstanceFor()
    {
        // mysqli
        $this->assertTrue($this->fixture->getInstanceFor('mysqli') instanceof AbstractAdapter);

        // PDO (mysql)
        $instance = $this->fixture->getInstanceFor('pdo', ['db_pdo_protocol' => 'mysql']);
        $this->assertTrue($instance instanceof AbstractAdapter);

        // PDO (sqlite)
        $instance = $this->fixture->getInstanceFor('pdo', ['db_pdo_protocol' => 'sqlite']);
        $this->assertTrue($instance instanceof AbstractAdapter);
    }

    public function testGetInstanceForInvalidAdapterName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown adapter name given. Currently supported are: mysqli, pdo');

        $this->fixture->getInstanceFor('invalid');
    }

    public function testGetInstanceForInvalidPDOProtocol()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only "mysql" protocol is supported at the moment.');

        $instance = $this->fixture->getInstanceFor('pdo', ['db_pdo_protocol' => 'invalid']);
        $this->assertFalse($instance instanceof AbstractAdapter);
    }

    /*
     * Tests for getSupportedAdapters
     */

    public function testGetSupportedAdapters()
    {
        $this->assertEquals(['mysqli', 'pdo'], $this->fixture->getSupportedAdapters());
    }
}
