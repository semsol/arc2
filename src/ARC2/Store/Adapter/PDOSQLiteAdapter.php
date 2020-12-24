<?php

/**
 * Adapter to enable usage of PDO functions.
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @author Konrad Abicht <hi@inspirito.de>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */

namespace ARC2\Store\Adapter;

use Exception;
use PDO;

/**
 * PDO SQLite Adapter, which only supports SQLite running in memory.
 */
class PDOSQLiteAdapter extends PDOAdapter
{
    public function checkRequirements()
    {
        if (false == \extension_loaded('pdo_sqlite')) {
            throw new Exception('Extension pdo_sqlite is not loaded.');
        }
    }

    /**
     * Connect to server or storing a given connection.
     *
     * @param PDO $existingConnection default is null
     */
    public function connect($existingConnection = null)
    {
        // reuse a given existing connection.
        // it assumes that $existingConnection is a PDO connection object
        if (null !== $existingConnection) {
            $this->db = $existingConnection;

        // create your own connection
        } elseif (false === $this->db instanceof PDO) {
            // set path to SQLite file
            if (
                isset($this->configuration['db_name'])
                && !empty($this->configuration['db_name'])
            ) {
                $dsn = 'sqlite:'.$this->configuration['db_name'];
            } else {
                // use in-memory
                $dsn = 'sqlite::memory:';
            }

            $this->db = new PDO($dsn);

            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // errors lead to exceptions
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // default fetch mode is associative
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            /*
             * define CONCAT function (otherwise SQLite will throw an exception)
             */
            $this->db->sqliteCreateFunction('CONCAT', function ($pattern, $string) {
                $result = '';

                foreach (\func_get_args() as $str) {
                    $result .= $str;
                }

                return $result;
            });

            /*
             * define REGEXP function (otherwise SQLite will throw an exception)
             */
            $this->db->sqliteCreateFunction('REGEXP', function ($pattern, $string) {
                if (0 < preg_match('/'.$pattern.'/i', $string)) {
                    return true;
                }

                return false;
            }, 2);
        }

        return $this->db;
    }

    public function deleteAllTables(): void
    {
        $this->exec(
            'SELECT "drop table " || name || ";"
               FROM sqlite_master
              WHERE type = "table";'
        );
    }

    /**
     * It gets all tables from the current database.
     */
    public function getAllTables(): array
    {
        $tables = $this->fetchList('SELECT name FROM sqlite_master WHERE type="table";');
        $result = [];
        foreach ($tables as $table) {
            // ignore SQLite tables
            if (false !== strpos($table['name'], 'sqlite_')) {
                continue;
            }
            $result[] = $table['name'];
        }

        return $result;
    }

    public function getCollation()
    {
        return '';
    }

    public function getConnectionId()
    {
        return null;
    }

    public function getDBSName()
    {
        return 'sqlite';
    }

    public function getServerInfo()
    {
        return null;
    }

    public function getServerVersion()
    {
        return $this->fetchRow('select sqlite_version()')['sqlite_version()'];
    }
}
