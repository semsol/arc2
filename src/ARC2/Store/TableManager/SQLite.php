<?php

/**
 * ARC2 RDF Store Table Manager.
 *
 * @license   W3C Software License and GPL
 * @author    Benjamin Nowack
 * @author    Konrad Abicht <hi@inspirito.de>
 *
 * @version   2010-11-16
 */

namespace ARC2\Store\TableManager;

use ARC2_Store;

class SQLite extends ARC2_Store
{
    public function __construct($a, &$caller)
    {
        parent::__construct($a, $caller);
    }

    public function createTables()
    {
        if (!$this->createTripleTable()) {
            return $this->addError('Could not create "triple" table ('.$this->a['db_object']->getErrorMessage().').');
        }
        if (!$this->createG2TTable()) {
            return $this->addError('Could not create "g2t" table ('.$this->a['db_object']->getErrorMessage().').');
        }
        if (!$this->createID2ValTable()) {
            return $this->addError('Could not create "id2val" table ('.$this->a['db_object']->getErrorMessage().').');
        }
        if (!$this->createS2ValTable()) {
            return $this->addError('Could not create "s2val" table ('.$this->a['db_object']->getErrorMessage().').');
        }
        if (!$this->createO2ValTable()) {
            return $this->addError('Could not create "o2val" table ('.$this->a['db_object']->getErrorMessage().').');
        }
        if (!$this->createSettingTable()) {
            return $this->addError('Could not create "setting" table ('.$this->a['db_object']->getErrorMessage().').');
        }

        return 1;
    }

    public function createTripleTable($suffix = 'triple')
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().$suffix.' (
            t INTEGER UNSIGNED NOT NULL UNIQUE,
            s INTEGER UNSIGNED NOT NULL,
            p INTEGER UNSIGNED NOT NULL,
            o INTEGER UNSIGNED NOT NULL,
            o_lang_dt INTEGER UNSIGNED NOT NULL,
            o_comp TEXT NOT NULL,                    -- normalized value for ORDER BY operations
            s_type INTEGER UNSIGNED NOT NULL DEFAULT 0,       -- uri/bnode => 0/1
            o_type INTEGER UNSIGNED NOT NULL DEFAULT 0,       -- uri/bnode/literal => 0/1/2
            misc INTEGER NOT NULL DEFAULT 0          -- temporary flags
        )';

        return $this->a['db_object']->exec($sql);
    }

    public function createG2TTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'g2t (
            g INTEGER UNSIGNED NOT NULL,
            t INTEGER UNSIGNED NOT NULL,
            UNIQUE (g,t)
        )';

        return $this->a['db_object']->exec($sql);
    }

    public function createID2ValTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'id2val (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            misc INTEGER UNSIGNED NOT NULL DEFAULT 0,
            val TEXT NOT NULL,
            val_type INTEGER NOT NULL DEFAULT 0,     -- uri/bnode/literal => 0/1/2
            UNIQUE (id,val_type)
        )';

        return $this->a['db_object']->exec($sql);
    }

    public function createS2ValTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'s2val (
            id INTEGER UNSIGNED NOT NULL,
            misc INTEGER NOT NULL DEFAULT 0,
            val_hash TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (id)
        )';

        return $this->a['db_object']->exec($sql);
    }

    public function createO2ValTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'o2val (
            id INTEGER NOT NULL,
            misc INTEGER UNSIGNED NOT NULL DEFAULT 0,
            val_hash TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (id)
        )';

        return $this->a['db_object']->exec($sql);
    }

    public function createSettingTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'setting (
            k TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (k)
        )';

        return $this->a['db_object']->exec($sql);
    }
}
