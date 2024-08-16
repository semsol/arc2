<?php

namespace Tests\unit\store;

use Tests\ARC2_TestCase;

class ARC2_StoreTest extends ARC2_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);
        $this->fixture->createDBCon();

        // remove all tables
        $this->fixture->getDBObject()->deleteAllTables();

        // fresh setup of ARC2
        $this->fixture->setup();
    }

    protected function tearDown(): void
    {
        $this->fixture->closeDBCon();
    }
}
