<?php

/**
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @author Konrad Abicht <konrad.abicht@pier-and-peer.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */

namespace ARC2\Store\Adapter;

abstract class AbstractAdapter
{
    protected $configuration;
    protected $db;

    /**
     * Stores errors of failed queries.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Sent queries.
     *
     * @var array
     */
    protected $queries = array();

    /**
     * @param array $configuration Default is array(). Only use, if you have your own mysqli connection.
     */
    public function __construct(array $configuration = array())
    {
        $this->configuration = $configuration;

        $this->checkRequirements();
    }

    abstract public function checkRequirements();

    abstract public function connect($existingConnection = null);

    abstract public function disconnect();

    abstract public function escape($value);

    abstract public function exec($sql);

    abstract public function fetchList($sql);

    abstract public function fetchRow($sql);

    abstract public function getAdapterName();

    abstract public function getCollation();

    abstract public function getDBSName();

    abstract public function getLastInsertId();

    abstract public function getServerInfo();

    abstract public function getErrorMessage();

    abstract public function getNumberOfRows($sql);

    abstract public function getStoreName();

    abstract public function getTablePrefix();

    abstract public function simpleQuery($sql);
}
