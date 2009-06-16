<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 Store Dumper
author:   Benjamin Nowack
version:  2008-07-02
*/

ARC2::inc('Class');

class ARC2_StoreDumper extends ARC2_Class {

  function __construct($a = '', &$caller) {
    parent::__construct($a, $caller);
  }
  
  function ARC2_StoreDumper($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->store =& $this->caller;
  }

  /*  */
  
  function dumpSPOG() {
    $con = $this->store->getDBCon();
    $rs = $this->getRecordset();
    if ($er = mysql_error($con)) $this->addError($er);
    header('Content-Type: application/sparql-results+xml');
    //header('Content-Type: text/plain');
    echo $this->getHeader();
    if ($rs) {
  		while ($row = mysql_fetch_array($rs)) {
        echo $this->getEntry($row);
      }
    }
    echo $this->getFooter();
  }
  
  /*  */
  
  function saveSPOG($path, $q = '') {
    $con = $this->store->getDBCon();
    $rs = $q ? $this->store->query($q, 'rows') : $this->getRecordset();
    if ($er = mysql_error($con)) return $this->addError($er);
    if (!$fp = @fopen($path, 'w')) return $this->addError('Could not create backup file at ' . realpath($path));
    fwrite($fp, $this->getHeader());
    if ($rs) {
      if ($q) {
        foreach ($rs as $row) {
          fwrite($fp, $this->getEntry($row));
        }
      }
      else {
    		while ($row = mysql_fetch_array($rs)) {
          fwrite($fp, $this->getEntry($row));
        }
      }
    }
    fwrite($fp, $this->getFooter());
    @fclose($fp);
  }
  
  /*  */

  function getRecordset() {
    $prefix = $this->store->getTablePrefix();
    $con = $this->store->getDBCon();
    $sql = '
      SELECT 
        VS.val AS s,
        T.s_type AS `s type`,
        VP.val AS p,
        0 AS `p type`,
        VO.val AS o,
        T.o_type AS `o type`,
        VLDT.val as `o lang_dt`,
        VG.val as g,
        0 AS `g type`
      FROM
        ' . $prefix . 'triple T
        JOIN ' . $prefix . 's2val VS ON (T.s = VS.id)
        JOIN ' . $prefix . 'id2val VP ON (T.p = VP.id)
        JOIN ' . $prefix . 'o2val VO ON (T.o = VO.id)
        JOIN ' . $prefix . 'id2val VLDT ON (T.o_lang_dt = VLDT.id)
        JOIN ' . $prefix . 'g2t G2T ON (T.t = G2T.t)
        JOIN ' . $prefix . 'id2val VG ON (G2T.g = VG.id)
    ';
    return mysql_unbuffered_query($sql, $con);
  }

  /*  */
  
  function getHeader() {
    $n = "\n";
    return '' .
      '<?xml version="1.0"?>' . 
      $n . '<sparql xmlns="http://www.w3.org/2005/sparql-results#">' .
      $n . '  <head>' .
      $n . '    <variable name="s"/>' .
      $n . '    <variable name="p"/>' .
      $n . '    <variable name="o"/>' .
      $n . '    <variable name="g"/>' .
      $n . '  </head>' .
      $n . '  <results>' .
    '';
  }

  function getEntry($row) {
    $n = "\n";
    $r = '';
    $r .= $n . '    <result>';
    foreach (array('s', 'p', 'o', 'g') as $var) {
      if (isset($row[$var])) {
        $type = (string) $row[$var . ' type'];
        $r .= $n . '      <binding name="' . $var . '">';
        if (($type == '0') || ($type == 'uri')) {
          $r .= $n . '        <uri>' . htmlspecialchars($row[$var]) . '</uri>';
        }
        elseif (($type == '1') || ($type == 'bnode')) {
          $r .= $n . '        <bnode>' . substr($row[$var], 2) . '</bnode>';
        }
        else {
          $lang_dt = '';
          foreach (array('o lang_dt', 'o lang', 'o datatype') as $k) {
            if (($var == 'o') && isset($row[$k]) && $row[$k]) $lang_dt = $row[$k];
          }
          $is_lang = preg_match('/^([a-z]+(\-[a-z0-9]+)*)$/i', $lang_dt);
          list($lang, $dt) = $is_lang ? array($lang_dt, '') : array('', $lang_dt);
          $lang = $lang ? ' xml:lang="' . $lang . '"' : '';
          $dt = $dt ? ' datatype="' . htmlspecialchars($dt) . '"' : '';
          $r .= $n . '        <literal' . $dt . $lang . '>' . htmlspecialchars($row[$var]) . '</literal>';
        }
        $r .= $n . '      </binding>';
      }
    }
    $r .= $n . '    </result>';
    return $r;
  }

  function getFooter() {
    $n = "\n";
    return '' .
      $n . '  </results>' .
      $n . '</sparql>' . 
      $n .
    '';
  }

  /*  */

}
