<?php

namespace ARC2\Store\Adapter;

class MysqliDbExtended extends \MysqliDb
{
    public function getAffectedRows()
    {
        return $this->mysqli()->affected_rows;
    }

    public function getErrorMessage()
    {
        return $this->mysqli()->error;
    }

    public function getServerInfo()
    {
        return $this->mysqli()->server_info;
    }

    public function plainQuery($sql)
    {
        return $this->mysqli()->query($sql);
    }
}
