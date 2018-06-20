<?php

namespace Tests\unit\src\ARC2\Store\Adapter;

use ARC2\Store\Adapter\CachedPDOAdapter;

class CachedPDOAdapterTest extends PDOAdapterTest
{
    protected function getAdapterInstance($configuration)
    {
        $configuration['cache_enabled'] = true;

        return new CachedPDOAdapter($configuration);
    }

    public function setUp()
    {
        parent::setUp();

        $this->fixture->clearCache();
    }

    public function tearDown()
    {
        $this->fixture->clearCache();

        parent::tearDown();
    }
}
