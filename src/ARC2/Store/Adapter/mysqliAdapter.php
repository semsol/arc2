<?php

/**
 * Adapter to enable usage of mysqli_* functions.
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @author Konrad Abicht <konrad.abicht@pier-and-peer.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */

namespace ARC2\Store\Adapter;

/**
 * mysqli Adapter - Handles database operations using mysqli.
 */
class mysqliAdapter extends AbstractAdapter
{
    protected $last_result;

    public function checkRequirements()
    {
        if (false == \extension_loaded('mysqli') || false == \function_exists('mysqli_connect')) {
            throw new \Exception('Extension mysqli is not loaded or function mysqli_connect is not available.');
        }
    }

    public function getAdapterName()
    {
        return 'mysqli';
    }

    /**
     * Connect to server or storing a given connection.
     *
     * @return string|MysqliDbExtended String if an error occoured, instance of MysqliDbExtended otherwise.
     */
    public function connect($existingConnection = null)
    {
        // reuse a given existing connection.
        // it assumes that $existingConnection is a mysqli connection object
        if (null !== $existingConnection) {
            $this->db = new MysqliDbExtended($existingConnection);

        // create your own connection
        } elseif (null == $this->db) {
            // connect
            try {
                $this->db = new MysqliDbExtended(
                    $this->configuration['db_host'],
                    $this->configuration['db_user'],
                    $this->configuration['db_pwd']
                );
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        if (isset($this->configuration['db_name'])
            && true !== $this->db->simpleQuery('USE `'.$this->configuration['db_name'].'`')) {
            $fixed = 0;
            /* try to create it */
            if ($this->configuration['db_name']) {
                $this->db->simpleQuery('
                  CREATE DATABASE IF NOT EXISTS `'.$this->configuration['db_name'].'`
                  DEFAULT CHARACTER SET utf8
                  DEFAULT COLLATE utf8_general_ci
                  '
                );
                if ($this->db->simpleQuery('USE `'.$this->configuration['db_name'].'`')) {
                    $this->db->simpleQuery("SET NAMES 'utf8'");
                    $fixed = 1;
                }
            }
            if (!$fixed) {
                return $this->addError($this->db->getErrorMessage());
            } else {
                if (preg_match('/^utf8/', $this->getCollation())) {
                    $this->db->simpleQuery("SET NAMES 'utf8'");
                }
                // This is RDF, we may need many JOINs...
                $this->db->simpleQuery('SET SESSION SQL_BIG_SELECTS=1');
            }
        }

        return $this->db;
    }

    public function disconnect()
    {
        return $this->db->disconnect();
    }

    public function escape($value)
    {
        return $this->db->escape($value);
    }

    public function fetchList($sql)
    {
        return $this->db->rawQuery($sql);
    }

    public function fetchRow($sql)
    {
        $row = $this->db->rawQueryOne($sql);

        return null != $row ? $row : false;
    }

    public function getCollation()
    {
        $row = $this->fetchRow('SHOW TABLE STATUS LIKE "'.$this->getTablePrefix().'setting"');

        if (isset($row['Collation'])) {
            return $row['Collation'];
        } else {
            return '';
        }
    }

    public function getConnectionId()
    {
        if (null != $this->db) {
            return $this->db->mysqli()->thread_id;
        }
    }

    /**
     * For backward compatibility reasons. Get mysqli connection object.
     *
     * @return mysqli
     */
    public function getConnection()
    {
        return $this->db->mysqli();
    }

    public function getDBSName()
    {
        if (null == $this->db) {
            return null;
        }

        return false !== strpos($this->getServerInfo(), 'MariaDB')
            ? 'mariadb'
            : 'mysql';
    }

    public function getLastInsertId()
    {
        if (null != $this->db) {
            return $this->db->getLastInsertId();
        }

        return 'No database connection (mysqliAdapter).';
    }

    public function getServerInfo()
    {
        $this->connect();

        return $this->db->mysqli()->server_info;
    }

    /**
     * Returns the version of the database server like 05-00-12
     */
    public function getServerVersion()
    {
        $res = preg_match(
            "/([0-9]+)\.([0-9]+)\.([0-9]+)/",
            $this->getServerInfo(),
            $matches
        );

        return 1 == $res
            ? sprintf('%02d-%02d-%02d', $matches[1], $matches[2], $matches[3])
            : '00-00-00';
    }

    public function getErrorMessage()
    {
        return $this->db->getErrorMessage();
    }

    public function getErrorCode()
    {
        return $this->db->getErrorCode();
    }

    public function getNumberOfRows($sql)
    {
        return $this->db->getNumberOfRows($sql);
    }

    public function getStoreName()
    {
        if (isset($this->configuration['store_name'])) {
            return $this->configuration['store_name'];
        }

        return 'arc';
    }

    public function getTablePrefix()
    {
        $prefix = '';
        if (isset($this->configuration['db_table_prefix'])) {
            $prefix = $this->configuration['db_table_prefix'].'_';
        }

        $prefix .= $this->getStoreName().'_';
        return $prefix;
    }

    /**
     * For compatibility reasons. Executes a query using mysqli and returns the result. Dont use
     * this function directly. It is only used once to make sure, ARC2 keeps its backward compatibility
     * while in the 2.x branch.
     *
     * @param string $sql Query to execute.
     *
     * @return mysqli result|false
     */
    public function mysqliQuery($sql)
    {
        return $this->db->mysqliQuery($sql);
    }

    /**
     * @param string $sql Query
     *
     * @return bool True if query ran fine, false otherwise.
     */
    public function simpleQuery($sql)
    {
        if (null == $this->db) {
            $this->connect();
        }

        return $this->db->simpleQuery($sql);
    }

    /**
     * @param string $sql Query with return of affected rows
     *
     * @return bool True if query ran fine, false otherwise.
     */
    public function exec($sql)
    {
        $this->db->simpleQuery($sql);
        return $this->db->getAffectedRows();
    }
}
