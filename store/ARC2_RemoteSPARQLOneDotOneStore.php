<?php
/**
 * ARC2 Remote SPARQL1.1 Store
 *
 * @author Benjamin Nowack <bnowack@semsol.com>, Mark Fichtner <m.fichtner@wiss-ki.eu>
 * @package ARC2
 * @version 2012-12-18
*/

ARC2::inc('Class');

/**
 * The purpose of this class is to connect some software running on arc2
 * to a SPARQL1.1 endpoint. This is currently tested with Sesame, but seems
 * to work fine up to now. Further work is needed - your support is welcome!
 */

class ARC2_RemoteSPARQLOneDotOneStore extends ARC2_Class {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
    $this->is_remote = 1;
  }
  
  function __init() {
    parent::__init();
  }

  /*  */

  function isSetUp() {
    return 1;
  }
  
  function setUp() {}

  function killDBProcesses() {}
  
  /*  */
  
  function reset() {
    return $this->runQuery('CLEAR ALL', 'clear');
  }
  
  function drop() {
    return $this->runQuery('DROP ALL', 'drop');
  }
  
  function insert($doc, $g, $keep_bnode_ids = 0) {

    return $this->query('INSERT INTO <' . $g . '> { ' . $this->toNTriples($doc, '', 1) . ' }');
  }
  
  function delete($doc, $g) {
    if (!$doc) {
      return $this->query('DELETE FROM <' . $g . '>');
    }
    else {
      return $this->query('DELETE FROM <' . $g . '> { ' . $this->toNTriples($doc, '', 1) . ' }');
    }
  }
  
  function replace($doc, $g, $doc_2) {
    return array($this->delete($doc, $g), $this->insert($doc_2, $g));
  }
  
  /*  */
  
  function query($q, $result_format = '', $src = '', $keep_bnode_ids = 0, $log_query = 0) {
    if ($log_query) $this->logQuery($q);
    ARC2::inc('SPARQLPlusParser');
    $p = new ARC2_SPARQLPlusParser($this->a, $this);
    $p->parse($q, $src);
    $infos = $p->getQueryInfos();
    $t1 = ARC2::mtime();
    if (!$errs = $p->getErrors()) {
      $qt = $infos['query']['type'];
      $r = array('query_type' => $qt, 'result' => $this->runQuery($q, $qt, $infos));
    }
    else {
      $r = array('result' => '');
    }
    $t2 = ARC2::mtime();
    $r['query_time'] = $t2 - $t1;
    /* query result */
    if ($result_format == 'raw') {
      return $r['result'];
    }
    if ($result_format == 'rows') {
      return $this->v('rows', array(), $r['result']);
    }
    if ($result_format == 'row') {
      if (!isset($r['result']['rows'])) return array();
      return $r['result']['rows'] ? $r['result']['rows'][0] : array();
    }
    return $r;
  }
  
  /* Transform a SPARQL 1.0 or SPARQL+/Update-Query to
   * SPARQL 1.1
   * @author Mark Fichtner
   */
  
  function transformSPARQLToOneDotOne($q, $infos) {
    if(empty($infos))
      return $q;
    else if($infos['query']['type'] == "load")
      $q = str_replace("INTO", "INTO GRAPH", $q);
    else if($infos['query']['type'] == "insert") {
      if(strpos($q, "WHERE") !== FALSE) 
        $q = str_replace("INTO", "{ GRAPH", $q) . "}";
      else
        $q = str_replace("INTO", "DATA { GRAPH", $q) . "}";
    } else if($infos['query']['type'] == "delete") {
      if(strpos($q, "FROM") !== FALSE)
        $q = str_replace("FROM", "{ GRAPH", $q) . "}";
      else {
        if(strpos($q, "WHERE") === FALSE)
//          $q = str_replace("DELETE ", "DELETE ", $q);
//        else
          $q = str_replace("DELETE ", "DELETE DATA ", $q);
      }
    }

//    $q = preg_replace("/LIMIT \d+/", "", $q); 
        
    return $q;
  
  }

  function runQuery($q, $qt = '', $infos = '') {

    /* ep */
    $ep = $this->v('remote_store_endpoint', 0, $this->a);
    if (!$ep) return false;
    /* prefixes */
    $q = $this->completeQuery($q);


    /* custom handling */
    $mthd = 'run' . $this->camelCase($qt) . 'Query';
    
    if (method_exists($this, $mthd)) {
      return $this->$mthd($q, $infos);
    }
    /* http verb */
    $mthd = in_array($qt, array('load', 'insert', 'delete', 'drop', 'clear')) ? 'POST' : 'GET';
    //$mthd = 'GET';
    
    $q = $this->transformSPARQLToOneDotOne($q, $infos);
    
    /* reader */
    ARC2::inc('Reader');
    $reader = new ARC2_Reader($this->a, $this);
    $reader->setAcceptHeader('Accept: application/sparql-results+xml; q=0.9, application/rdf+xml; q=0.9, */*; q=0.1');
    if ($mthd == 'GET') {
      $url = $ep;
      $url .= strpos($ep, '?') ? '&' : '?';
      $url .= 'query=' . urlencode($q);
      if ($k = $this->v('store_read_key', '', $this->a)) $url .= '&key=' . urlencode($k);
    }
    if ($mthd != 'GET' || strlen($url) > 255) {
      $mthd = 'POST';
      $url = $ep;
      $reader->setHTTPMethod($mthd);
      //$reader->setCustomHeaders("Content-Type: application/x-www-form-urlencoded");
      $reader->setCustomHeaders("Content-Type: application/x-www-form-urlencoded; charset=utf-8");
      $suffix = ($k = $this->v('store_write_key', '', $this->a)) ? '&key=' . rawurlencode($k) : '';
      if(in_array($qt, array('load', 'insert', 'delete', 'drop', 'clear')))
//        $reader->setMessageBody('update=' . rawurlencode($q) . $suffix);
        $reader->setMessageBody('update=' .  rawurlencode(utf8_encode($q)) . $suffix);
      else
        $reader->setMessageBody('query=' . rawurlencode(utf8_encode($q)) . $suffix);
//        $reader->setMessageBody('query=' . rawurlencode($q) . $suffix);
    }
    $to = $this->v('remote_store_timeout', 0, $this->a);
    $reader->activate($url, '', 0, $to);
    $format = $reader->getFormat();
    $resp = '';
    while ($d = $reader->readStream()) {
      $resp .= $this->toUTF8($d);
    }

    $reader->closeStream();
    $ers = $reader->getErrors();
    $this->a['reader_auth_infos'] = $reader->getAuthInfos();
    unset($this->reader);
    if ($ers) return array('errors' => $ers);
    $mappings = array('rdfxml' => 'RDFXML', 'sparqlxml' => 'SPARQLXMLResult', 'turtle' => 'Turtle');
    if (!$format || !isset($mappings[$format])) {
      return $resp;
    }

    // Return raw data from endpoint if passthrough_FORMAT specified
    $passthrough = $this->v('passthrough_sparqlxml', false, $this->a);
    if ($passthrough && isset($infos['output']) && $infos['output'] == 'SPARQLXML') {
      return $resp;
    }

    /* format parser */
    $suffix = $mappings[$format] . 'Parser';
    ARC2::inc($suffix);
    $cls = 'ARC2_' . $suffix;
    $parser = new $cls($this->a, $this);
    $parser->parse($ep, $resp);
    /* ask|load|insert|delete */
    if (in_array($qt, array('ask', 'load', 'insert', 'delete', 'drop', 'clear'))) {
      $bid = $parser->getBooleanInsertedDeleted();
      if ($qt == 'ask') {
        $r = $bid['boolean'];
      }
      else {
        $r = $bid;
      }
    }
    /* select */
    elseif (($qt == 'select') && !method_exists($parser, 'getRows')) {
      $r = $resp;
    }
    elseif ($qt == 'select') {
      $r = array('rows' => $parser->getRows(), 'variables' => $parser->getVariables());
    }
    /* any other */
    else {
      $r = $parser->getSimpleIndex(0);
    }
    unset($parser);
    return $r;
  }
  
  /*  */
  
  function optimizeTables() {}
  
  /*  */

  function getResourceLabel($res, $unnamed_label = 'An unnamed resource') {
    if (!isset($this->resource_labels)) $this->resource_labels = array();
    if (isset($this->resource_labels[$res])) return $this->resource_labels[$res];
    if (!preg_match('/^[a-z0-9\_]+\:[^\s]+$/si', $res)) return $res;/* literal */
    $r = '';
    if (preg_match('/^\_\:/', $res)) {
      return $unnamed_label;
    }
    $row = $this->query('SELECT ?o WHERE { <' . $res . '> ?p ?o . FILTER(REGEX(str(?p), "(label|name)$", "i"))}', 'row');
    if ($row) {
      $r = $row['o'];
    }
    else {
      $r = preg_replace("/^(.*[\/\#])([^\/\#]+)$/", '\\2', str_replace('#self', '', $res));
      $r = str_replace('_', ' ', $r);
      $r = preg_replace('/([a-z])([A-Z])/e', '"\\1 " . strtolower("\\2")', $r);
    }
    $this->resource_labels[$res] = $r;
    return $r;
  }
  
  function getDomains($p) {
    $r = array();
    foreach($this->query('SELECT DISTINCT ?type WHERE {?s <' . $p . '> ?o ; a ?type . }', 'rows') as $row) {
      $r[] = $row['type'];
    }
    return $r;
  }

  /*  */
  
}
