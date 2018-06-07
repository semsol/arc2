<?php

namespace ARC2\Store\Adapter;

class MysqliDbExtended extends \MysqliDb
{
    protected $last_result;

    public function getAffectedRows()
    {
        return $this->mysqli()->affected_rows;
    }

    public function getErrorMessage()
    {
        return $this->mysqli()->error;
    }

    public function getNumberOfRows($sql = null)
    {
        if (null != $sql) {
            $result = $this->query($sql);
            return is_object($result) ? $result->num_rows : 0;

        } elseif (is_object($this->last_result)) {
            return $this->last_result->num_rows;
        }

        return 0;
    }

    public function getServerInfo()
    {
        return $this->mysqli()->server_info;
    }

    public function plainQuery($sql)
    {
        return $this->mysqli()->query($sql);
    }

    public function query($sql, $num_rows = null)
    {
        $this->last_result = $this->mysqli()->query($sql, $num_rows);
        return $this->last_result ? true : false;
    }
}
