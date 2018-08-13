<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\CachedPDOAdapter;

class CachedPDOAdapterTest extends PDOAdapterTest
{
    public function setUp()
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cache not enabled, therefore CachedPDOAdapter can not be used.
     */
    public function testExceptionIfCacheIsNotEnabled()
    {
        $cfg = $this->dbConfig;
        unset($cfg['cache_enabled']);
        unset($cfg['cache_instance']);

        new CachedPDOAdapter($cfg);
    }
}
