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
class PDOSQLite extends PDOAdapter
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
     * @param EasyDB $existingConnection default is null
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
        }

        return $this->db;
    }

    public function deleteAllTables(): void
    {
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
        return null;
    }

    public function getServerInfo()
    {
        return null;
    }

    public function getServerVersion()
    {
        return null;
    }
}
