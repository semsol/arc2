<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class ARC2_TestCase extends TestCase
{
    /**
     * Store configuration to connect with the database.
     *
     * @var array
     */
    protected $dbConfig;

    /**
     * Subject under test.
     *
     * @var mixed
     */
    protected $fixture;

    protected function setUp(): void
    {
        global $dbConfig;

        $this->dbConfig = $dbConfig;

        // in case we run with a cache, clear it
        if (
            isset($this->dbConfig['cache_instance'])
            && $this->dbConfig['cache_instance'] instanceof CacheInterface
        ) {
            $this->dbConfig['cache_instance']->clear();
        }
    }

    protected function tearDown(): void
    {
        // in case we run with a cache, clear it
        if (
            isset($this->dbConfig['cache_instance'])
            && $this->dbConfig['cache_instance'] instanceof CacheInterface
        ) {
            $this->dbConfig['cache_instance']->clear();
        }

        parent::tearDown();
    }
}
