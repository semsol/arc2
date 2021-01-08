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

    public function createTables(): void
    {
        $this->createTripleTable();
        $this->createG2TTable();
        $this->createID2ValTable();
        $this->createS2ValTable();
        $this->createO2ValTable();
        $this->createSettingTable();
    }

    public function createTripleTable($suffix = 'triple'): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().$suffix.' (
            t INTEGER UNSIGNED NOT NULL UNIQUE,
            s INTEGER UNSIGNED NOT NULL,
            p INTEGER UNSIGNED NOT NULL,
            o INTEGER UNSIGNED NOT NULL,
            o_lang_dt INTEGER UNSIGNED NOT NULL,
            o_comp TEXT NOT NULL,                       -- normalized value for ORDER BY operations
            s_type INTEGER UNSIGNED NOT NULL DEFAULT 0, -- uri/bnode => 0/1
            o_type INTEGER UNSIGNED NOT NULL DEFAULT 0, -- uri/bnode/literal => 0/1/2
            misc INTEGER NOT NULL DEFAULT 0             -- temporary flags
        )';

        $this->a['db_object']->exec($sql);
    }

    public function createG2TTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'g2t (
            g INTEGER UNSIGNED NOT NULL,
            t INTEGER UNSIGNED NOT NULL,
            UNIQUE (g,t)
        )';

        $this->a['db_object']->exec($sql);
    }

    public function createID2ValTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'id2val (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            misc INTEGER UNSIGNED NOT NULL DEFAULT 0,
            val TEXT NOT NULL,
            val_type INTEGER NOT NULL DEFAULT 0,     -- uri/bnode/literal => 0/1/2
            UNIQUE (id,val_type)
        )';

        $this->a['db_object']->exec($sql);
    }

    public function createS2ValTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'s2val (
            id INTEGER UNSIGNED NOT NULL,
            misc INTEGER NOT NULL DEFAULT 0,
            val_hash TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (id)
        )';

        $this->a['db_object']->exec($sql);
    }

    public function createO2ValTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'o2val (
            id INTEGER NOT NULL,
            misc INTEGER UNSIGNED NOT NULL DEFAULT 0,
            val_hash TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (id)
        )';

        $this->a['db_object']->exec($sql);
    }

    public function createSettingTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$this->getTablePrefix().'setting (
            k TEXT NOT NULL,
            val TEXT NOT NULL,
            UNIQUE (k)
        )';

        $this->a['db_object']->exec($sql);
    }
}
