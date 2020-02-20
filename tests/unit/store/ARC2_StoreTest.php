<?php

namespace Tests\unit\store;

use Tests\ARC2_TestCase;

class ARC2_StoreTest extends ARC2_TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);
        $this->fixture->createDBCon();

        // remove all tables
        $tables = $this->fixture->getDBObject()->fetchList('SHOW TABLES');
        foreach($tables as $table) {
            $this->fixture->getDBObject()->simpleQuery('DROP TABLE '. $table['Tables_in_'.$this->dbConfig['db_name']]);
        }

        // fresh setup of ARC2
        $this->fixture->setup();
    }

    public function tearDown(): void
    {
        $this->fixture->closeDBCon();
    }

    public function testCacheEnabled()
    {
        $cacheEnabled = isset($this->dbConfig['cache_enabled'])
            && $this->dbConfig['cache_enabled']
            && 'pdo' == $this->dbConfig['db_adapter'];
        $this->assertEquals($cacheEnabled, $this->fixture->cacheEnabled());
    }
}
