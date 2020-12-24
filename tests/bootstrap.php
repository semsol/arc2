<?php

require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);

require 'ARC2_TestHandler.php';

global $dbConfig;

$dbConfig = null;

/*
 * For local development only.
 *
 * Copy config.php.dist to config.php, adapt your values
 * and run PHPUnit.
 */
if (file_exists(__DIR__.'/config.php')) {
    $dbConfig = require 'config.php';
} else {
    /*
     * For CI only.
     *
     * Parameter are set from outside using environment variables.
     * Please check YAML files in .github/workflows for details.
     */
    $mysqlTestConfig = [
        'db_name' => 'arc2_test',
        'db_user' => 'root',
        'db_pwd'  => 'Pass123',
        'db_host' => '127.0.0.1',
        'db_port' => $_SERVER['DB_PORT'] ?: 3306,
    ];

    /**
     * DB Adapter (PDO or mysqli)
     */
    $dbAdapter = getenv('DB_ADAPTER') ?? $_SERVER['DB_ADAPTER'];
    if ('pdo' == $dbAdapter) {
        $dbConfig['db_adapter'] = 'pdo';
        $dbConfig['db_pdo_protocol'] = getenv('DB_PDO_PROTOCOL') ?? $_SERVER['DB_PDO_PROTOCOL'];

        if (empty($dbConfig['db_pdo_protocol'])) {
            throw new \Exception(
                'Environment variable DB_PDO_PROTOCOL not set. Possible values are: mysql, sqlite'
            );
        }
    } elseif ('mysqli' == $dbAdapter) {
        $dbConfig['db_adapter'] = 'mysqli';
    } else {
        throw new Exception('Neither environment variable DB_ADAPTER nor $_SERVER["DB_ADAPTER"] are set.');
    }

    // set defaults for dbConfig entries
    if (false == isset($dbConfig['store_name'])) {
        $dbConfig['store_name'] = 'arc';
    }

    if (false == isset($dbConfig['db_table_prefix'])) {
        $dbConfig['db_table_prefix'] = null;
    }

    // SQLite in memory only: unset db_name to force it to use sqlite::memory:
    $useMemory = getenv('DB_USE_MEMORY') ?? $_SERVER['DB_USE_MEMORY'];
    if ('true' == $useMemory) {
        unset($dbConfig['db_name']);
    }

    /*
    * set cache enable
    *
    * if enabled, we use an instance of ArrayCache which is very fast
    */
    if (true === getenv('CACHE_ENABLED') || 'true' == getenv('CACHE_ENABLED')) {
        $dbConfig['cache_enabled'] = true;
        $dbConfig['cache_instance'] = new Symfony\Component\Cache\Simple\ArrayCache();
    }
}
