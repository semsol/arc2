<?php

namespace Tests;

use Psr\SimpleCache\CacheInterface;

class ARC2_TestCase extends \PHPUnit\Framework\TestCase
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

    public function setUp(): void
    {
        global $dbConfig;

        $this->dbConfig = $dbConfig;

        // in case we run with a cache, clear it
        if (isset($this->dbConfig['cache_instance']) && $this->dbConfig['cache_instance'] instanceof CacheInterface) {
            $this->dbConfig['cache_instance']->clear();
        }
    }

    public function tearDown(): void
    {
        // in case we run with a cache, clear it
        if (isset($this->dbConfig['cache_instance']) && $this->dbConfig['cache_instance'] instanceof CacheInterface) {
            $this->dbConfig['cache_instance']->clear();
        }

        parent::tearDown();
    }
}
