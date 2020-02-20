<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\CachedPDOAdapter;

class CachedPDOAdapterTest extends PDOAdapterTest
{
    public function setUp(): void
    {
        global $dbConfig;

        if (false == isset($dbConfig['cache_enabled'])
            || (
                isset($dbConfig['cache_enabled']) && false == $dbConfig['cache_enabled']
            )) {
            $this->markTestSkipped('Cache not enabled, therefore skipped.');
        }

        parent::setUp();
    }

    protected function getAdapterInstance($configuration)
    {
        return new CachedPDOAdapter($configuration);
    }

    public function testExceptionIfCacheIsNotEnabled()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Cache not enabled, therefore CachedPDOAdapter can not be used.');

        $cfg = $this->dbConfig;
        unset($cfg['cache_enabled']);
        unset($cfg['cache_instance']);

        new CachedPDOAdapter($cfg);
    }
}
