<?php
/**
 * ARC2 RDF Store.
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 */
ARC2::inc('Class');

class ARC2_Store extends ARC2_Class
{
    public function __construct($a, &$caller)
    {
        parent::__construct($a, $caller);
    }

    public function __init()
    {/* db_con */
        parent::__init();
        $this->table_lock = 0;
        $this->triggers = $this->v('store_triggers', [], $this->a);
        $this->queue_queries = $this->v('store_queue_queries', 0, $this->a);
        $this->is_win = ('win' == strtolower(substr(PHP_OS, 0, 3))) ? true : false;
        $this->max_split_tables = $this->v('store_max_split_tables', 10, $this->a);
        $this->split_predicates = $this->v('store_split_predicates', [], $this->a);
    }

    public function getName()
    {
        return $this->v('store_name', 'arc', $this->a);
    }

    public function getTablePrefix()
    {
        if (!isset($this->tbl_prefix)) {
            $r = $this->v('db_table_prefix', '', $this->a);
            $r .= $r ? '_' : '';
            $r .= $this->getName().'_';
            $this->tbl_prefix = $r;
        }

        return $this->tbl_prefix;
    }

    public function createDBCon()
    {
        foreach (['db_host' => 'localhost', 'db_user' => '', 'db_pwd' => '', 'db_name' => ''] as $k => $v) {
            $this->a[$k] = $this->v($k, $v, $this->a);
        }
        if (!$db_con = mysqli_connect($this->a['db_host'], $this->a['db_user'], $this->a['db_pwd'])) {
            return $this->addError(mysqli_error($db_con));
        }
        $this->a['db_con'] = $db_con;
        if (!mysqli_query($db_con, 'USE `'.$this->a['db_name'].'`')) {
            $fixed = 0;
            /* try to create it */
            if ($this->a['db_name']) {
                $this->queryDB('
          CREATE DATABASE IF NOT EXISTS `'.$this->a['db_name'].'`
          DEFAULT CHARACTER SET utf8
          DEFAULT COLLATE utf8_general_ci
          ', $db_con, 1
        );
                if (mysqli_query($db_con, 'USE `'.$this->a['db_name'].'`')) {
                    $this->queryDB("SET NAMES 'utf8'", $db_con);
                    $fixed = 1;
                }
            }
            if (!$fixed) {
                return $this->addError(mysqli_error($db_con));
            }
        }
        if (preg_match('/^utf8/', $this->getCollation())) {
            $this->queryDB("SET NAMES 'utf8'", $db_con);
        }
        // This is RDF, we may need many JOINs...
        $this->queryDB('SET SESSION SQL_BIG_SELECTS=1', $db_con);

        return true;
    }

    public function getDBCon($force = 0)
    {
        if ($force || !isset($this->a['db_con'])) {
            if (!$this->createDBCon()) {
                return false;
            }
        }
        if (!$force && !mysqli_thread_id($this->a['db_con'])) {
            return $this->getDBCon(1);
        }

        return $this->a['db_con'];
    }

    /**
     * @todo make property $a private, but provide access via a getter
     */
    public function closeDBCon()
    {
        if ($this->v('db_con', false, $this->a)) {
            mysqli_close($this->a['db_con']);
        }
        unset($this->a['db_con']);
    }

    public function getDBVersion()
    {
        if (!$this->v('db_version')) {
            $this->db_version = preg_match("/^([0-9]+)\.([0-9]+)\.([0-9]+)/", mysqli_get_server_info($this->getDBCon()), $m) ? sprintf('%02d-%02d-%02d', $m[1], $m[2], $m[3]) : '00-00-00';
        }

        return $this->db_version;
    }

    public function getCollation()
    {
        $rs = $this->queryDB('SHOW TABLE STATUS LIKE "'.$this->getTablePrefix().'setting"', $this->getDBCon());

        return ($rs && ($row = mysqli_fetch_array($rs)) && isset($row['Collation'])) ? $row['Collation'] : '';
    }

    public function getColumnType()
    {
        if (!$this->v('column_type')) {
            $tbl = $this->getTablePrefix().'g2t';
            $rs = $this->queryDB('SHOW COLUMNS FROM '.$tbl.' LIKE "t"', $this->getDBCon());
            $row = $rs ? mysqli_fetch_array($rs) : ['Type' => 'mediumint'];
            $this->column_type = preg_match('/mediumint/', $row['Type']) ? 'mediumint' : 'int';
        }

        return $this->column_type;
    }

    public function hasHashColumn($tbl)
    {
        $var_name = 'has_hash_column_'.$tbl;
        if (!isset($this->$var_name)) {
            $tbl = $this->getTablePrefix().$tbl;
            $rs = $this->queryDB('SHOW COLUMNS FROM '.$tbl.' LIKE "val_hash"', $this->getDBCon());
            $this->$var_name = ($rs && mysqli_fetch_array($rs));
        }

        return $this->$var_name;
    }

    public function hasFulltextIndex()
    {
        if (!isset($this->has_fulltext_index)) {
            $this->has_fulltext_index = 0;
            $tbl = $this->getTablePrefix().'o2val';
            $rs = $this->queryDB('SHOW INDEX FROM '.$tbl, $this->getDBCon());
            while ($row = mysqli_fetch_array($rs)) {
                if ('val' != $row['Column_name']) {
                    continue;
                }
                if ('FULLTEXT' != $row['Index_type']) {
                    continue;
                }
                $this->has_fulltext_index = 1;
                break;
            }
        }

        return $this->has_fulltext_index;
    }

    public function enableFulltextSearch()
    {
        if ($this->hasFulltextIndex()) {
            return 1;
        }
        $tbl = $this->getTablePrefix().'o2val';
        $this->queryDB('CREATE FULLTEXT INDEX vft ON '.$tbl.'(val(128))', $this->getDBCon(), 1);
    }

    public function disableFulltextSearch()
    {
        if (!$this->hasFulltextIndex()) {
            return 1;
        }
        $tbl = $this->getTablePrefix().'o2val';
        $this->queryDB('DROP INDEX vft ON '.$tbl, $this->getDBCon());
    }

    public function countDBProcesses()
    {
        return ($rs = $this->queryDB('SHOW PROCESSLIST', $this->getDBCon())) ? mysqli_num_rows($rs) : 0;
    }

    public function killDBProcesses($needle = '', $runtime = 30)
    {
        $dbcon = $this->getDBCon();
        /* make sure needle is sql */
        if (preg_match('/\?.+ WHERE/i', $needle, $m)) {
            $needle = $this->query($needle, 'sql');
        }
        $rs = $this->queryDB('SHOW FULL PROCESSLIST', $dbcon);
        $ref_tbl = $this->getTablePrefix().'triple';
        while ($row = mysqli_fetch_array($rs)) {
            if ($row['Time'] < $runtime) {
                continue;
            }
            if (!preg_match('/^\s*(INSERT|SELECT) /s', $row['Info'])) {
                continue;
            } /* only basic queries */
            if (!strpos($row['Info'], $ref_tbl.' ')) {
                continue;
            } /* only from this store */
            $kill = 0;
            if ($needle && (false !== strpos($row['Info'], $needle))) {
                $kill = 1;
            }
            if (!$needle) {
                $kill = 1;
            }
            if (!$kill) {
                continue;
            }
            $this->queryDB('KILL '.$row['Id'], $dbcon);
        }
    }

    public function getTables()
    {
        return ['triple', 'g2t', 'id2val', 's2val', 'o2val', 'setting'];
    }

    public function isSetUp()
    {
        if (($con = $this->getDBCon())) {
            $tbl = $this->getTablePrefix().'setting';

            return $this->queryDB('SELECT 1 FROM '.$tbl.' LIMIT 0', $con) ? 1 : 0;
        }
    }

    public function setUp($force = 0)
    {
        if (($force || !$this->isSetUp()) && ($con = $this->getDBCon())) {
            if ($this->getDBVersion() < '04-00-04') {
                /* UPDATE + JOINs */
                return $this->addError('MySQL version not supported. ARC requires version 4.0.4 or higher.');
            }
            ARC2::inc('StoreTableManager');
            $mgr = new ARC2_StoreTableManager($this->a, $this);
            $mgr->createTables();
        }
    }

    public function extendColumns()
    {
        ARC2::inc('StoreTableManager');
        $mgr = new ARC2_StoreTableManager($this->a, $this);
        $mgr->extendColumns();
        $this->column_type = 'int';
    }

    public function splitTables()
    {
        ARC2::inc('StoreTableManager');
        $mgr = new ARC2_StoreTableManager($this->a, $this);
        $mgr->splitTables();
    }

    public function hasSetting($k)
    {
        $tbl = $this->getTablePrefix().'setting';
        $sql = 'SELECT val FROM '.$tbl." WHERE k = '".md5($k)."'";
        $rs = $this->queryDB($sql, $this->getDBCon());

        return ($rs && ($row = mysqli_fetch_array($rs))) ? 1 : 0;
    }

    public function getSetting($k, $default = 0)
    {
        $tbl = $this->getTablePrefix().'setting';
        $sql = 'SELECT val FROM '.$tbl." WHERE k = '".md5($k)."'";
        $rs = $this->queryDB($sql, $this->getDBCon());
        if ($rs && ($row = mysqli_fetch_array($rs))) {
            return unserialize($row['val']);
        }

        return $default;
    }

    public function setSetting($k, $v)
    {
        $con = $this->getDBCon();
        $tbl = $this->getTablePrefix().'setting';
        if ($this->hasSetting($k)) {
            $sql = 'UPDATE '.$tbl." SET val = '".mysqli_real_escape_string($con, serialize($v))."' WHERE k = '".md5($k)."'";
        } else {
            $sql = 'INSERT INTO '.$tbl." (k, val) VALUES ('".md5($k)."', '".mysqli_real_escape_string($con, serialize($v))."')";
        }

        return $this->queryDB($sql, $con);
    }

    public function removeSetting($k)
    {
        $tbl = $this->getTablePrefix().'setting';

        return $this->queryDB('DELETE FROM '.$tbl." WHERE k = '".md5($k)."'", $this->getDBCon());
    }

    public function getQueueTicket()
    {
        if (!$this->queue_queries) {
            return 1;
        }
        $t = 'ticket_'.substr(md5(uniqid(rand())), 0, 10);
        $con = $this->getDBCon();
        /* lock */
        $rs = $this->queryDB('LOCK TABLES '.$this->getTablePrefix().'setting WRITE', $con);
        /* queue */
        $queue = $this->getSetting('query_queue', []);
        $queue[] = $t;
        $this->setSetting('query_queue', $queue);
        $this->queryDB('UNLOCK TABLES', $con);
        /* loop */
        $lc = 0;
        $queue = $this->getSetting('query_queue', []);
        while ($queue && ($queue[0] != $t) && ($lc < 30)) {
            if ($this->is_win) {
                sleep(1);
                ++$lc;
            } else {
                usleep(100000);
                $lc += 0.1;
            }
            $queue = $this->getSetting('query_queue', []);
        }

        return ($lc < 30) ? $t : 0;
    }

    public function removeQueueTicket($t)
    {
        if (!$this->queue_queries) {
            return 1;
        }
        $con = $this->getDBCon();
        /* lock */
        $this->queryDB('LOCK TABLES '.$this->getTablePrefix().'setting WRITE', $con);
        /* queue */
        $vals = $this->getSetting('query_queue', []);
        $pos = array_search($t, $vals);
        $queue = ($pos < (count($vals) - 1)) ? array_slice($vals, $pos + 1) : [];
        $this->setSetting('query_queue', $queue);
        $this->queryDB('UNLOCK TABLES', $con);
    }

    public function reset($keep_settings = 0)
    {
        $con = $this->getDBCon();
        $tbls = $this->getTables();
        $prefix = $this->getTablePrefix();
        /* remove split tables */
        $ps = $this->getSetting('split_predicates', []);
        foreach ($ps as $p) {
            $tbl = 'triple_'.abs(crc32($p));
            $this->queryDB('DROP TABLE '.$prefix.$tbl, $con);
        }
        $this->removeSetting('split_predicates');
        /* truncate tables */
        foreach ($tbls as $tbl) {
            if ($keep_settings && ('setting' == $tbl)) {
                continue;
            }
            $this->queryDB('TRUNCATE '.$prefix.$tbl, $con);
        }
    }

    public function drop()
    {
        $con = $this->getDBCon();
        $tbls = $this->getTables();
        $prefix = $this->getTablePrefix();
        foreach ($tbls as $tbl) {
            $this->queryDB('DROP TABLE '.$prefix.$tbl, $con);
        }
    }

    public function insert($doc, $g, $keep_bnode_ids = 0)
    {
        $doc = is_array($doc) ? $this->toTurtle($doc) : $doc;
        $infos = ['query' => ['url' => $g, 'target_graph' => $g]];
        ARC2::inc('StoreLoadQueryHandler');
        $h = new ARC2_StoreLoadQueryHandler($this->a, $this);
        $r = $h->runQuery($infos, $doc, $keep_bnode_ids);
        $this->processTriggers('insert', $infos);

        return $r;
    }

    public function delete($doc, $g)
    {
        if (!$doc) {
            $infos = ['query' => ['target_graphs' => [$g]]];
            ARC2::inc('StoreDeleteQueryHandler');
            $h = new ARC2_StoreDeleteQueryHandler($this->a, $this);
            $r = $h->runQuery($infos);
            $this->processTriggers('delete', $infos);

            return $r;
        }
    }

    public function replace($doc, $g, $doc_2)
    {
        return [$this->delete($doc, $g), $this->insert($doc_2, $g)];
    }

    public function dump()
    {
        ARC2::inc('StoreDumper');
        $d = new ARC2_StoreDumper($this->a, $this);
        $d->dumpSPOG();
    }

    public function createBackup($path, $q = '')
    {
        ARC2::inc('StoreDumper');
        $d = new ARC2_StoreDumper($this->a, $this);
        $d->saveSPOG($path, $q);
    }

    public function renameTo($name)
    {
        $con = $this->getDBCon();
        $tbls = $this->getTables();
        $old_prefix = $this->getTablePrefix();
        $new_prefix = $this->v('db_table_prefix', '', $this->a);
        $new_prefix .= $new_prefix ? '_' : '';
        $new_prefix .= $name.'_';
        foreach ($tbls as $tbl) {
            $rs = $this->queryDB('RENAME TABLE '.$old_prefix.$tbl.' TO '.$new_prefix.$tbl, $con);
            $err = mysqli_error($con);
            if (!empty($err)) {
                return $this->addError($err);
            }
        }
        $this->a['store_name'] = $name;
        unset($this->tbl_prefix);
    }

    public function replicateTo($name)
    {
        $conf = array_merge($this->a, ['store_name' => $name]);
        $new_store = ARC2::getStore($conf);
        $new_store->setUp();
        $new_store->reset();
        $con = $this->getDBCon();
        $tbls = $this->getTables();
        $old_prefix = $this->getTablePrefix();
        $new_prefix = $new_store->getTablePrefix();
        foreach ($tbls as $tbl) {
            $rs = $this->queryDB('INSERT IGNORE INTO '.$new_prefix.$tbl.' SELECT * FROM '.$old_prefix.$tbl, $con);
            $err = mysqli_error($con);
            if (!empty($err)) {
                return $this->addError($err);
            }
        }

        return $new_store->query('SELECT COUNT(*) AS t_count WHERE { ?s ?p ?o}', 'row');
    }

    public function query($q, $result_format = '', $src = '', $keep_bnode_ids = 0, $log_query = 0)
    {
        if ($log_query) {
            $this->logQuery($q);
        }
        $con = $this->getDBCon();
        if (preg_match('/^dump/i', $q)) {
            $infos = ['query' => ['type' => 'dump']];
        } else {
            ARC2::inc('SPARQLPlusParser');
            $p = new ARC2_SPARQLPlusParser($this->a, $this);
            $p->parse($q, $src);
            $infos = $p->getQueryInfos();
        }
        if ('infos' == $result_format) {
            return $infos;
        }
        $infos['result_format'] = $result_format;
        if (!isset($p) || !$p->getErrors()) {
            $qt = $infos['query']['type'];
            if (!in_array($qt, ['select', 'ask', 'describe', 'construct', 'load', 'insert', 'delete', 'dump'])) {
                return $this->addError('Unsupported query type "'.$qt.'"');
            }
            $t1 = ARC2::mtime();
            $r = ['query_type' => $qt, 'result' => $this->runQuery($infos, $qt, $keep_bnode_ids, $q)];
            $t2 = ARC2::mtime();
            $r['query_time'] = $t2 - $t1;
            /* query result */
            if ('raw' == $result_format) {
                return $r['result'];
            }
            if ('rows' == $result_format) {
                return $r['result']['rows'] ? $r['result']['rows'] : [];
            }
            if ('row' == $result_format) {
                return $r['result']['rows'] ? $r['result']['rows'][0] : [];
            }

            return $r;
        }

        return 0;
    }

    public function runQuery($infos, $type, $keep_bnode_ids = 0, $q = '')
    {
        ARC2::inc('Store'.ucfirst($type).'QueryHandler');
        $cls = 'ARC2_Store'.ucfirst($type).'QueryHandler';
        $h = new $cls($this->a, $this);
        $ticket = 1;
        $r = [];
        if ($q && ('select' == $type)) {
            $ticket = $this->getQueueTicket($q);
        }
        if ($ticket) {
            if ('load' == $type) {/* the LoadQH supports raw data as 2nd parameter */
                $r = $h->runQuery($infos, '', $keep_bnode_ids);
            } else {
                $r = $h->runQuery($infos, $keep_bnode_ids);
            }
        }
        if ($q && ('select' == $type)) {
            $this->removeQueueTicket($ticket);
        }
        $trigger_r = $this->processTriggers($type, $infos);

        return $r;
    }

    public function processTriggers($type, $infos)
    {
        $r = [];
        $trigger_defs = $this->triggers;
        $this->triggers = [];
        $triggers = $this->v($type, [], $trigger_defs);
        if ($triggers) {
            $r['trigger_results'] = [];
            $triggers = is_array($triggers) ? $triggers : [$triggers];
            $trigger_inc_path = $this->v('store_triggers_path', '', $this->a);
            foreach ($triggers as $trigger) {
                $trigger .= !preg_match('/Trigger$/', $trigger) ? 'Trigger' : '';
                if (ARC2::inc(ucfirst($trigger), $trigger_inc_path)) {
                    $cls = 'ARC2_'.ucfirst($trigger);
                    $config = array_merge($this->a, ['query_infos' => $infos]);
                    $trigger_obj = new $cls($config, $this);
                    if (method_exists($trigger_obj, 'go')) {
                        $r['trigger_results'][] = $trigger_obj->go();
                    }
                }
            }
        }
        $this->triggers = $trigger_defs;

        return $r;
    }

    public function getValueHash($val, $_32bit = false)
    {
        $hash = crc32($val);
        if ($_32bit && ($hash & 0x80000000)) {
            $hash = sprintf('%u', $hash);
        }
        $hash = abs($hash);

        return $hash;
    }

    public function getTermID($val, $term = '')
    {
        /* mem cache */
        if (!isset($this->term_id_cache) || (count(array_keys($this->term_id_cache)) > 100)) {
            $this->term_id_cache = [];
        }
        if (!isset($this->term_id_cache[$term])) {
            $this->term_id_cache[$term] = [];
        }
        $tbl = preg_match('/^(s|o)$/', $term) ? $term.'2val' : 'id2val';
        /* cached? */
        if ((strlen($val) < 100) && isset($this->term_id_cache[$term][$val])) {
            return $this->term_id_cache[$term][$val];
        }
        $con = $this->getDBCon();
        $r = 0;
        /* via hash */
        if (preg_match('/^(s2val|o2val)$/', $tbl) && $this->hasHashColumn($tbl)) {
            $sql = 'SELECT id, val FROM '.$this->getTablePrefix().$tbl." WHERE val_hash = '".$this->getValueHash($val)."' ORDER BY id";
            $rs = $this->queryDB($sql, $con);
            if (!$rs || !mysqli_num_rows($rs)) {// try 32 bit version
                $sql = 'SELECT id, val FROM '.$this->getTablePrefix().$tbl." WHERE val_hash = '".$this->getValueHash($val, true)."' ORDER BY id";
                $rs = $this->queryDB($sql, $con);
            }
            if (($rs = $this->queryDB($sql, $con)) && mysqli_num_rows($rs)) {
                while ($row = mysqli_fetch_array($rs)) {
                    if ($row['val'] == $val) {
                        $r = $row['id'];
                        break;
                    }
                }
            }
        }
        /* exact match */
        else {
            $sql = 'SELECT id FROM '.$this->getTablePrefix().$tbl." WHERE val = BINARY '".mysqli_real_escape_string($con, $val)."' LIMIT 1";
            if (($rs = $this->queryDB($sql, $con)) && mysqli_num_rows($rs) && ($row = mysqli_fetch_array($rs))) {
                $r = $row['id'];
            }
        }
        if ($r && (strlen($val) < 100)) {
            $this->term_id_cache[$term][$val] = $r;
        }

        return $r;
    }

    public function getIDValue($id, $term = '')
    {
        $tbl = preg_match('/^(s|o)$/', $term) ? $term.'2val' : 'id2val';
        $con = $this->getDBCon();
        $sql = 'SELECT val FROM '.$this->getTablePrefix().$tbl.' WHERE id = '.mysqli_real_escape_string($con, $id).' LIMIT 1';
        if (($rs = $this->queryDB($sql, $con)) && mysqli_num_rows($rs) && ($row = mysqli_fetch_array($rs))) {
            return $row['val'];
        }

        return 0;
    }

    public function getLock($t_out = 10, $t_out_init = '')
    {
        if (!$t_out_init) {
            $t_out_init = $t_out;
        }
        $con = $this->getDBCon();
        $l_name = $this->a['db_name'].'.'.$this->getTablePrefix().'.write_lock';
        $rs = $this->queryDB('SELECT IS_FREE_LOCK("'.$l_name.'") AS success', $con);
        if ($rs) {
            $row = mysqli_fetch_array($rs);
            if (!$row['success']) {
                if ($t_out) {
                    sleep(1);

                    return $this->getLock($t_out - 1, $t_out_init);
                }
            } else {
                $rs = $this->queryDB('SELECT GET_LOCK("'.$l_name.'", '.$t_out_init.') AS success', $con);
                if ($rs) {
                    $row = mysqli_fetch_array($rs);

                    return $row['success'];
                }
            }
        }

        return 0;
    }

    public function releaseLock()
    {
        $con = $this->getDBCon();

        return $this->queryDB('DO RELEASE_LOCK("'.$this->a['db_name'].'.'.$this->getTablePrefix().'.write_lock")', $con);
    }

    public function processTables($level = 2, $operation = 'optimize')
    {/* 1: triple + g2t, 2: triple + *2val, 3: all tables */
        $con = $this->getDBCon();
        $pre = $this->getTablePrefix();
        $tbls = $this->getTables();
        $sql = '';
        foreach ($tbls as $tbl) {
            if (($level < 3) && preg_match('/(backup|setting)$/', $tbl)) {
                continue;
            }
            if (($level < 2) && preg_match('/(val)$/', $tbl)) {
                continue;
            }
            $sql .= $sql ? ', ' : strtoupper($operation).' TABLE ';
            $sql .= $pre.$tbl;
        }
        $this->queryDB($sql, $con);
        $err = mysqli_error($con);
        if (!empty($err)) {
            $this->addError($err.' in '.$sql);
        }
    }

    public function optimizeTables($level = 2)
    {
        if ($this->v('ignore_optimization')) {
            return 1;
        }

        return $this->processTables($level, 'optimize');
    }

    public function checkTables($level = 2)
    {
        return $this->processTables($level, 'check');
    }

    public function repairTables($level = 2)
    {
        return $this->processTables($level, 'repair');
    }

    public function changeNamespaceURI($old_uri, $new_uri)
    {
        ARC2::inc('StoreHelper');
        $c = new ARC2_StoreHelper($this->a, $this);

        return $c->changeNamespaceURI($old_uri, $new_uri);
    }

    public function getResourceLabel($res, $unnamed_label = 'An unnamed resource')
    {
        if (!isset($this->resource_labels)) {
            $this->resource_labels = [];
        }
        if (isset($this->resource_labels[$res])) {
            return $this->resource_labels[$res];
        }
        if (!preg_match('/^[a-z0-9\_]+\:[^\s]+$/si', $res)) {
            return $res;
        } /* literal */
        $ps = $this->getLabelProps();
        if ($this->getSetting('store_label_properties', '-') != md5(serialize($ps))) {
            $this->inferLabelProps($ps);
        }
        //$sub_q .= $sub_q ? ' || ' : '';
        //$sub_q .= 'REGEX(str(?p), "(last_name|name|fn|title|label)$", "i")';
        $q = 'SELECT ?label WHERE { <'.$res.'> ?p ?label . ?p a <http://semsol.org/ns/arc#LabelProperty> } LIMIT 3';
        $r = '';
        $rows = $this->query($q, 'rows');
        foreach ($rows as $row) {
            $r = strlen($row['label']) > strlen($r) ? $row['label'] : $r;
        }
        if (!$r && preg_match('/^\_\:/', $res)) {
            return $unnamed_label;
        }
        $r = $r ? $r : preg_replace("/^(.*[\/\#])([^\/\#]+)$/", '\\2', str_replace('#self', '', $res));
        $r = str_replace('_', ' ', $r);
        $r = preg_replace_callback('/([a-z])([A-Z])/', function ($matches) {
            return $matches[1].' '.strtolower($matches[2]);
        }, $r);
        $this->resource_labels[$res] = $r;

        return $r;
    }

    public function getLabelProps()
    {
        return array_merge(
            $this->v('rdf_label_properties', [], $this->a),
            [
                'http://www.w3.org/2000/01/rdf-schema#label',
                'http://xmlns.com/foaf/0.1/name',
                'http://purl.org/dc/elements/1.1/title',
                'http://purl.org/rss/1.0/title',
                'http://www.w3.org/2004/02/skos/core#prefLabel',
                'http://xmlns.com/foaf/0.1/nick',
            ]
        );
    }

    public function inferLabelProps($ps)
    {
        $this->query('DELETE FROM <label-properties>');
        $sub_q = '';
        foreach ($ps as $p) {
            $sub_q .= ' <'.$p.'> a <http://semsol.org/ns/arc#LabelProperty> . ';
        }
        $this->query('INSERT INTO <label-properties> { '.$sub_q.' }');
        $this->setSetting('store_label_properties', md5(serialize($ps)));
    }

    public function getResourcePredicates($res)
    {
        $r = [];
        $rows = $this->query('SELECT DISTINCT ?p WHERE { <'.$res.'> ?p ?o . }', 'rows');
        foreach ($rows as $row) {
            $r[$row['p']] = [];
        }

        return $r;
    }

    public function getDomains($p)
    {
        $r = [];
        foreach ($this->query('SELECT DISTINCT ?type WHERE {?s <'.$p.'> ?o ; a ?type . }', 'rows') as $row) {
            $r[] = $row['type'];
        }

        return $r;
    }

    public function getPredicateRange($p)
    {
        $row = $this->query('SELECT ?val WHERE {<'.$p.'> rdfs:range ?val . } LIMIT 1', 'row');

        return $row ? $row['val'] : '';
    }

    /**
     * @param string $q
     *
     * @todo make file path configurable
     * @todo add try/catch in case file creation/writing fails
     */
    public function logQuery($q)
    {
        $fp = fopen('arc_query_log.txt', 'a');
        fwrite($fp, date('Y-m-d\TH:i:s\Z', time()).' : '.$q.''."\n\n");
        fclose($fp);
    }
}
