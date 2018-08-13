<?php

namespace ARC2\Store\Adapter;

/**
 * Extends MysqliDb class with some convient functions.
 */
class MysqliDbExtended extends \MysqliDb
{
    protected $last_result;

    /**
     * Returns the number of affected rows, if you ran a query using simpleQuery before.
     *
     * @return int Affected rows by an UPDATE/DELETE query.
     */
    public function getAffectedRows()
    {
        return $this->mysqli()->affected_rows;
    }

    /**
     * If you ran a query using MysqliDbExtended::simpleQuery and an error occoured, you can
     * get the error code with this function.
     *
     * @return int Error code, if available.
     */
    public function getErrorCode()
    {
        return $this->mysqli()->errno;
    }

    /**
     * If you ran a query using MysqliDbExtended::simpleQuery and an error occoured, you can
     * get the error message with this function.
     *
     * @return string Non-empty string, if an error occoured, empty string otherwise.
     */
    public function getErrorMessage()
    {
        return $this->mysqli()->error;
    }

    /**
     * @return int
     */
    public function getLastInsertId()
    {
        if (is_object($this->last_result)) {
            return $this->last_result->insert_id;
        }

        return null;
    }

    /**
     * Executes a SQL statement and returns the number of rows. This function will return 0,
     * regardless of errors in the query.
     *
     * @param string $sql Query to execute.
     *
     * @return int Number of rows, if available, 0 otherwise.
     */
    public function getNumberOfRows($sql = null)
    {
        if (null != $sql) {
            $result = $this->mysqli()->query($sql);
            return is_object($result) ? $result->num_rows : 0;

        } elseif (is_object($this->last_result)) {
            return $this->last_result->num_rows;
        }

        return 0;
    }

    /**
     * Returns the server version.
     *
     * @return string
     */
    public function getServerInfo()
    {
        return $this->mysqli()->server_info;
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
        return $this->mysqli()->query($sql);
    }

    /**
     * @param string $sql Query to execute.
     *
     * @return bool True if query runs without problems, false otherwise.
     */
    public function simpleQuery($sql, $num_rows = null)
    {
        $this->last_result = $this->mysqli()->query($sql, $num_rows);
        return $this->last_result ? true : false;
    }
}
