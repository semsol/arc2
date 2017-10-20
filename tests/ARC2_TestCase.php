<?php

namespace Tests;

class ARC2_TestCase extends \PHPUnit\Framework\TestCase
{
    protected $dbConfig;

    public function setUp()
    {
        global $dbConfig;

        $this->dbConfig = $dbConfig;
    }
}
