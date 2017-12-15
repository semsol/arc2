<?php

namespace Tests;

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

    public function setUp()
    {
        global $dbConfig;

        $this->dbConfig = $dbConfig;
    }
}
