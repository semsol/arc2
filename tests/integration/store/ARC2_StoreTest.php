<?php

namespace Tests\integration;

use Tests\ARC2_TestCase;

class ARC2_StoreTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);
    }

    public function testSetup()
    {
        $this->fixture->reset();

        $this->fixture->setup();
    }
}
