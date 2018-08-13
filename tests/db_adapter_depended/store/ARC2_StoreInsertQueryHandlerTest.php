<?php

namespace Tests\db_adapter_depended\store;

use Tests\ARC2_TestCase;

class ARC2_StoreInsertQueryHandlerTest extends ARC2_TestCase
{
    protected $store;

    public function setUp()
    {
        parent::setUp();

        $this->store = \ARC2::getStore($this->dbConfig);
        $this->store->drop();
        $this->store->setup();

        $this->fixture = new \ARC2_StoreInsertQueryHandler($this->store->a, $this->store);
    }

    public function tearDown()
    {
        $this->store->closeDBCon();
    }

    /*
     * Tests for __init
     */

    /**
     * @doesNotPerformAssertions
     */
    public function test__init()
    {
        $this->fixture = new \ARC2_StoreInsertQueryHandler($this->store->a, $this->store);
        $this->fixture->__init();
        $this->assertEquals($this->store, $this->fixture->store);
    }
}
