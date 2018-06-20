<?php

require_once __DIR__ .'/../vendor/autoload.php';

error_reporting(E_ALL);

require 'ARC2_TestHandler.php';

global $dbConfig;

if (file_exists(__DIR__ .'/config.php')) {
    // use custom DB credentials, if available
    $dbConfig = require 'config.php';

} else {
    // standard DB credentials (ready to use in Travis)
    $dbConfig = array(
        'db_name' => 'testdb',
        'db_user' => 'root',
        'db_pwd'  => '',
        'db_host' => '127.0.0.1',
    );
}

// set defaults for dbConfig entries
if (false == isset($dbConfig['store_name'])) {
    $dbConfig['store_name'] = 'arc';
}

if (false == isset($dbConfig['db_table_prefix'])) {
    $dbConfig['db_table_prefix'] = null;
}

// set DB adapter (see related phpunit-xx.xml file), possible values: mysqli, pdo
if ('pdo' == getenv('DB_ADAPTER')) {
    if (!empty(getenv('DB_PDO_PROTOCOL'))) {
        $dbConfig['db_adapter'] = 'pdo';
        $dbConfig['db_pdo_protocol'] = getenv('DB_PDO_PROTOCOL');
    } else {
        throw new \Exception('Environment variable DB_PDO_PROTOCOL not set. Possible values are: mysql');
    }
} else {
    $dbConfig['db_adapter'] = 'mysqli';
}

/*
 * set cache enable
 *
 * if enabled, we use an instance of ArrayCache which is very fast
 */
if (true ===\getenv('CACHE_ENABLED') || 'true' == \getenv('CACHE_ENABLED')) {
    $dbConfig['cache_enabled'] = true;
    $dbConfig['cache_instance'] = new Symfony\Component\Cache\Simple\ArrayCache();
}
