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
    protected $db;

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
        // build connection credential array
        foreach (['db_host' => 'localhost', 'db_user' => '', 'db_pwd' => '', 'db_name' => ''] as $k => $v) {
            $this->a[$k] = $this->v($k, $v, $this->a);
        }

        // connect
        try {
            $this->db = new \ARC2\Store\Adapter\MysqliDbExtended($this->a['db_host'], $this->a['db_user'], $this->a['db_pwd']);
        } catch (\Exception $e) {
            return $this->addError($e->getMessage());
        }

        $this->a['db_con'] = $this->db->mysqli();
        $this->a['db_object'] = $this->db;

        if (true !== $this->db->simpleQuery('USE `'.$this->a['db_name'].'`')) {
            $fixed = 0;
            /* try to create it */
            if ($this->a['db_name']) {
                $this->db->simpleQuery('
                  CREATE DATABASE IF NOT EXISTS `'.$this->a['db_name'].'`
                  DEFAULT CHARACTER SET utf8
                  DEFAULT COLLATE utf8_general_ci
                  '
                );
                if ($this->db->simpleQuery('USE `'.$this->a['db_name'].'`')) {
                    $this->db->simpleQuery("SET NAMES 'utf8'");
                    $fixed = 1;
                }
            }
            if (!$fixed) {
                return $this->addError($this->db->getErrorMessage());
            }
        }
        if (preg_match('/^utf8/', $this->getCollation())) {
            $this->db->simpleQuery("SET NAMES 'utf8'");
        }
        // This is RDF, we may need many JOINs...
        $this->db->simpleQuery('SET SESSION SQL_BIG_SELECTS=1');

        return true;
    }

    public function getDBObject()
    {
        return $this->db;
    }

    public function getDBCon($force = 0)
    {
        if ($force || !isset($this->a['db_con'])) {
            if (!$this->createDBCon()) {
                return false;
            }
        }

        return $this->a['db_con'];
    }

    /**
     * @todo make property $a private, but provide access via a getter
     */
    public function closeDBCon()
    {
        if ($this->v('db_con', false, $this->a)) {
            $this->db->disconnect();
        }
        unset($this->a['db_con']);
    }

    public function getDBVersion()
    {
        if (!$this->v('db_version')) {
            // connect, if no connection available
            if (null == $this->db) {
                $this->createDBCon();
            }

            $res = preg_match(
                "/^([0-9]+)\.([0-9]+)\.([0-9]+)/",
                $this->db->getServerInfo(),
                $m
            );
            $this->db_version = $res ? sprintf('%02d-%02d-%02d', $m[1], $m[2], $m[3]) : '00-00-00';
        }

        return $this->db_version;
    }

    /**
     * @return string Returns DBS name. Possible values: mysql, mariadb
     */
    public function getDBSName()
    {
        return false !== strpos($this->a['db_con']->server_info, 'MariaDB')
            ? 'mariadb'
            : 'mysql';
    }

    public function getCollation()
    {
        $row = $this->db->rawQueryOne('SHOW TABLE STATUS LIKE "'.$this->getTablePrefix().'setting"');
        return isset($row['Collation']) ? $row['Collation'] : '';
    }

    public function getColumnType()
    {
        if (!$this->v('column_type')) {
            $tbl = $this->getTablePrefix().'g2t';

            $row = $this->db->rawQueryOne('SHOW COLUMNS FROM '.$tbl.' LIKE "t"');
            if (null == $row) {
                $row = ['Type' => 'mediumint'];
            }

            $this->column_type = preg_match('/mediumint/', $row['Type']) ? 'mediumint' : 'int';
        }

        return $this->column_type;
    }

    public function hasHashColumn($tbl)
    {
        $var_name = 'has_hash_column_'.$tbl;
        if (!isset($this->$var_name)) {
            $tbl = $this->getTablePrefix().$tbl;

            $row = $this->db->rawQueryOne('SHOW COLUMNS FROM '.$tbl.' LIKE "val_hash"');
            $this->$var_name = null !== $row;
        }

        return $this->$var_name;
    }

    public function hasFulltextIndex()
    {
        if (!isset($this->has_fulltext_index)) {
            $this->has_fulltext_index = 0;
            $tbl = $this->getTablePrefix().'o2val';

            $rows = $this->db->rawQuery('SHOW INDEX FROM '.$tbl);
            foreach($rows as $row) {
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
        $this->db->simpleQuery('CREATE FULLTEXT INDEX vft ON '.$tbl.'(val(128))');
    }

    public function disableFulltextSearch()
    {
        if (!$this->hasFulltextIndex()) {
            return 1;
        }
        $tbl = $this->getTablePrefix().'o2val';
        $this->db->simpleQuery('DROP INDEX vft ON '.$tbl);
    }

    public function countDBProcesses()
    {
        return $this->db->getNumberOfRows('SHOW PROCESSLIST');
    }

    /**
     * Manipulating database processes using ARC2 is discouraged.
     *
     * @deprecated
     */
    public function killDBProcesses($needle = '', $runtime = 30)
    {
        /* make sure needle is sql */
        if (preg_match('/\?.+ WHERE/i', $needle, $m)) {
            $needle = $this->query($needle, 'sql');
        }
        $ref_tbl = $this->getTablePrefix().'triple';

        $rows = $this->db->rawQuery('SHOW FULL PROCESSLIST');
        foreach ($rows as $row) {
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
            $this->db->simpleQuery('KILL '.$row['Id']);
        }
    }

    public function getTables()
    {
        return ['triple', 'g2t', 'id2val', 's2val', 'o2val', 'setting'];
    }

    public function isSetUp()
    {
        if (null !== $this->db) {
            $tbl = $this->getTablePrefix().'setting';

            return $this->db->simpleQuery('SELECT 1 FROM '.$tbl.' LIMIT 0') ? 1 : 0;
        }

        return 0;
    }

    public function setUp($force = 0)
    {
        if (($force || !$this->isSetUp()) && $this->getDBCon()) {
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

        $row = $this->db->rawQueryOne('SELECT val FROM '.$tbl." WHERE k = '".md5($k)."'");
        if (null !== $row) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getSetting($k, $default = 0)
    {
        $tbl = $this->getTablePrefix().'setting';
        $row = $this->db->rawQueryOne('SELECT val FROM '.$tbl." WHERE k = '".md5($k)."'");
        if (isset($row['val'])) {
            return unserialize($row['val']);
        }

        return $default;
    }

    public function setSetting($k, $v)
    {
        $tbl = $this->getTablePrefix().'setting';
        if ($this->hasSetting($k)) {
            $sql = 'UPDATE '.$tbl." SET val = '".$this->db->escape(serialize($v))."' WHERE k = '".md5($k)."'";
        } else {
            $sql = 'INSERT INTO '.$tbl." (k, val) VALUES ('".md5($k)."', '".$this->db->escape(serialize($v))."')";
        }

        return $this->db->simpleQuery($sql);
    }

    public function removeSetting($k)
    {
        $tbl = $this->getTablePrefix().'setting';

        return $this->db->simpleQuery('DELETE FROM '.$tbl." WHERE k = '".md5($k)."'");
    }

    public function getQueueTicket()
    {
        if (!$this->queue_queries) {
            return 1;
        }
        $t = 'ticket_'.substr(md5(uniqid(rand())), 0, 10);
        /* lock */
        $this->db->simpleQuery('LOCK TABLES '.$this->getTablePrefix().'setting WRITE');
        /* queue */
        $queue = $this->getSetting('query_queue', []);
        $queue[] = $t;
        $this->setSetting('query_queue', $queue);
        $this->db->simpleQuery('UNLOCK TABLES');
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
        /* lock */
        $this->db->simpleQuery('LOCK TABLES '.$this->getTablePrefix().'setting WRITE');
        /* queue */
        $vals = $this->getSetting('query_queue', []);
        $pos = array_search($t, $vals);
        $queue = ($pos < (count($vals) - 1)) ? array_slice($vals, $pos + 1) : [];
        $this->setSetting('query_queue', $queue);
        $this->db->simpleQuery('UNLOCK TABLES');
    }

    public function reset($keep_settings = 0)
    {
        $tbls = $this->getTables();
        $prefix = $this->getTablePrefix();
        /* remove split tables */
        $ps = $this->getSetting('split_predicates', []);
        foreach ($ps as $p) {
            $tbl = 'triple_'.abs(crc32($p));
            $this->db->simpleQuery('DROP TABLE '.$prefix.$tbl);
        }
        $this->removeSetting('split_predicates');
        /* truncate tables */
        foreach ($tbls as $tbl) {
            if ($keep_settings && ('setting' == $tbl)) {
                continue;
            }
            $this->db->simpleQuery('TRUNCATE '.$prefix.$tbl);
        }
    }

    public function drop()
    {
        if (null == $this->db) {
            $this->createDBCon();
        }

        $tbls = $this->getTables();
        $prefix = $this->getTablePrefix();
        foreach ($tbls as $tbl) {
            $this->db->simpleQuery('DROP TABLE '.$prefix.$tbl);
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
        $tbls = $this->getTables();
        $old_prefix = $this->getTablePrefix();
        $new_prefix = $this->v('db_table_prefix', '', $this->a);
        $new_prefix .= $new_prefix ? '_' : '';
        $new_prefix .= $name.'_';
        foreach ($tbls as $tbl) {
            $this->db->simpleQuery('RENAME TABLE '.$old_prefix.$tbl.' TO '.$new_prefix.$tbl);
            if (!empty($this->db->getErrorMessage())) {
                return $this->addError($this->db->getErrorMessage());
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
        $tbls = $this->getTables();
        $old_prefix = $this->getTablePrefix();
        $new_prefix = $new_store->getTablePrefix();
        foreach ($tbls as $tbl) {
            $this->db->simpleQuery('INSERT IGNORE INTO '.$new_prefix.$tbl.' SELECT * FROM '.$old_prefix.$tbl);
            if (!empty($this->db->getErrorMessage())) {
                return $this->addError($this->db->getErrorMessage());
            }
        }

        return $new_store->query('SELECT COUNT(*) AS t_count WHERE { ?s ?p ?o}', 'row');
    }

    public function query($q, $result_format = '', $src = '', $keep_bnode_ids = 0, $log_query = 0)
    {
        if ($log_query) {
            $this->logQuery($q);
        }
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
        $r = 0;
        /* via hash */
        if (preg_match('/^(s2val|o2val)$/', $tbl) && $this->hasHashColumn($tbl)) {

            $rows = $this->db->rawQuery(
                'SELECT id, val FROM '.$this->getTablePrefix().$tbl." WHERE val_hash = '".$this->getValueHash($val)."' ORDER BY id"
            );
            if (is_array($rows) && 0 < count($rows)) {
                foreach($rows as $row) {
                    if ($row['val'] == $val) {
                        $r = $row['id'];
                        break;
                    }
                }
            }
        }
        /* exact match */
        else {
            $sql = 'SELECT id FROM '.$this->getTablePrefix().$tbl." WHERE val = BINARY '".$this->db->escape($val)."' LIMIT 1";
            $row = $this->db->rawQueryOne($sql);

            if (null !== $row && isset($row['id'])) {
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
        $row = $this->db->rawQueryOne(
            'SELECT val FROM '.$this->getTablePrefix().$tbl.' WHERE id = '.$this->db->escape($id).' LIMIT 1'
        );
        if (isset($row['val'])) {
            return $row['val'];
        }

        return 0;
    }

    public function getLock($t_out = 10, $t_out_init = '')
    {
        if (!$t_out_init) {
            $t_out_init = $t_out;
        }

        $l_name = $this->a['db_name'].'.'.$this->getTablePrefix().'.write_lock';
        $row = $this->db->rawQueryOne('SELECT IS_FREE_LOCK("'.$l_name.'") AS success');

        if (is_array($row)) {
            if (!$row['success']) {
                if ($t_out) {
                    sleep(1);

                    return $this->getLock($t_out - 1, $t_out_init);
                }
            } else {
                $row = $this->db->rawQueryOne('SELECT GET_LOCK("'.$l_name.'", '.$t_out_init.') AS success');
                if (isset($row['success'])) {
                    return $row['success'];
                }
            }
        }

        return 0;
    }

    public function releaseLock()
    {
        return $this->db->simpleQuery('DO RELEASE_LOCK("'.$this->a['db_name'].'.'.$this->getTablePrefix().'.write_lock")');
    }

    public function processTables($level = 2, $operation = 'optimize')
    {
        /*
         * level:
         *      1. triple + g2t
         *      2. triple + *2val
         *      3. all tables
         */
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
        $this->db->simpleQuery($sql);
        if (false == empty($this->db->getErrorMessage())) {
            $this->addError($this->db->getErrorMessage().' in '.$sql);
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

    /**
     * @param string $res URI
     * @param string $unnamed_label How to label a resource without a name?
     *
     * @return string
     */
    public function getResourceLabel($res, $unnamed_label = 'An unnamed resource')
    {
        // init local label cache, if not set
        if (!isset($this->resource_labels)) {
            $this->resource_labels = [];
        }
        // if we already know the label for the given resource
        if (isset($this->resource_labels[$res])) {
            return $this->resource_labels[$res];
        }
        // if no URI was given, assume its a literal and return it
        if (!preg_match('/^[a-z0-9\_]+\:[^\s]+$/si', $res)) {
            return $res;
        }

        $ps = $this->getLabelProps();
        if ($this->getSetting('store_label_properties', '-') != md5(serialize($ps))) {
            $this->inferLabelProps($ps);
        }

        foreach ($ps as $labelProperty) {
            // send a query for each label property
            $result = $this->query('SELECT ?label WHERE { <'.$res.'> <'.$labelProperty.'> ?label }');
            if (isset($result['result']['rows'][0])) {
                $this->resource_labels[$res] = $result['result']['rows'][0]['label'];
                return $result['result']['rows'][0]['label'];
            }
        }

        $r = preg_replace("/^(.*[\/\#])([^\/\#]+)$/", '\\2', str_replace('#self', '', $res));
        $r = str_replace('_', ' ', $r);
        $r = preg_replace_callback('/([a-z])([A-Z])/', function ($matches) {
            return $matches[1].' '.strtolower($matches[2]);
        }, $r);
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
