<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 base class
author:   Benjamin Nowack
version:  2009-03-31 (Addition: resetErrors method
          2009-05-28 switch from getScriptURI to getRequestURI in init()
          2009-06-22 refactored PName methods
*/

class ARC2_Class {
  
  function __construct($a = '', &$caller) {
    $a = is_array($a) ? $a : array();
    $this->a = $a;
    $this->caller = &$caller;
    $this->__init();
  }
  
  function ARC2_Class($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {/* base, time_limit */
    $this->inc_path = ARC2::getIncPath();
    $this->ns_count = 0;
    $this->nsp = array('http://www.w3.org/1999/02/22-rdf-syntax-ns#' => 'rdf');
    $this->used_ns = array('http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $this->ns = $this->v('ns', array(), $this->a);

    $this->base = $this->v('base', ARC2::getRequestURI(), $this->a);
    $this->errors = array();
    $this->warnings = array();
    $this->adjust_utf8 = $this->v('adjust_utf8', 0, $this->a);
  }

  /*  */
  
  function v($name, $default = false, $o = false) {/* value if set */
    $o = ($o !== false) ? $o : $this;
    if (is_array($o)) {
      return isset($o[$name]) ? $o[$name] : $default;
    }
    return isset($o->$name) ? $o->$name : $default;
  }
  
  function v1($name, $default = false, $o = false) {/* value if 1 (= not empty) */
    $o = ($o !== false) ? $o : $this;
    if (is_array($o)) {
      return (isset($o[$name]) && $o[$name]) ? $o[$name] : $default;
    }
    return (isset($o->$name) && $o->$name) ? $o->$name : $default;
  }
  
  function m($name, $a = false, $default = false, $o = false) {/* call method */
    $o = ($o !== false) ? $o : $this;
    return method_exists($o, $name) ? $o->$name($a) : $default;
  }

  /*  */

  function camelCase($v, $lc_first = 0) {
    $r = ucfirst($v);
    while (preg_match('/^(.*)[\-\_ ](.*)$/', $r, $m)) {
      $r = $m[1] . ucfirst($m[2]);
    }
    return $lc_first ? strtolower(substr($r, 0, 1)) . substr($r, 1) : $r;
  }

  /*  */
  
  function addError($v) {
    if (!in_array($v, $this->errors)) {
      $this->errors[] = $v;
    }
    if ($this->caller && method_exists($this->caller, 'addError')) {
      $glue = strpos($v, ' in ') ? ' via ' : ' in ';
      $this->caller->addError($v . $glue . get_class($this));
    }
    return false;
  }
  
  function getErrors() {
    return $this->errors;
  }
  
  function getWarnings() {
    return $this->warnings;
  }

  function resetErrors() {
    $this->errors = array();
    if ($this->caller && method_exists($this->caller, 'resetErrors')) {
      $this->caller->resetErrors();
    }
  }
  
  /*  */
  
  function splitURI($v) {
    return ARC2::splitURI($v);
  }

  /*  */

  function getPName($v, $connector = ':') {
    /* is already a pname */
    if ($ns = $this->getPNameNamespace($v)) {
      if (!in_array($ns, $this->used_ns)) $this->used_ns[] = $ns;
      return $v;
    }
    /* new pname */
    if ($parts = $this->splitURI($v)) {
      /* known prefix */
      foreach ($this->ns as $prefix => $ns) {
        if ($parts[0] == $ns) {
          return $prefix . $connector . $parts[1];
        }
      }
      /* new prefix */
      $prefix = $this->getPrefix($parts[0]);
      return $prefix . $connector . $parts[1];
    }
    return $v;
  }

  function getPNameNamespace($v) {
    if (!preg_match('/^([a-z0-9\_\-]+)\:([a-z0-9\_\-\.\%]*)$/i', $v, $m)) return 0;
    if (!isset($this->ns[$m[1]])) return 0;
    return $this->ns[$m[1]];
  }

  function getPrefix($ns) {
    if (!isset($this->nsp[$ns])) {
      $this->ns['ns' . $this->ns_count] = $ns;
      $this->nsp[$ns] = 'ns' . $this->ns_count;
      $this->ns_count++;
    }
    if (!in_array($ns, $this->used_ns)) $this->used_ns[] = $ns;
    return $this->nsp[$ns];
  }

  function expandPName($v) {
    if (preg_match('/^([a-z0-9\_\-]+)\:([a-z0-9\_\-]+)$/i', $v, $m) && isset($this->ns[$m[1]])) {
      return $this->ns[$m[1]] . $m[2];
    }
    return $v;
  }

  function expandPNames($index) {
    $r = array();
    foreach ($index as $s => $ps) {
      $s = $this->expandPName($s);
      $r[$s] = array();
      foreach ($ps as $p => $os) {
        $p = $this->expandPName($p);
        if (!is_array($os)) $os = array($os);
        foreach ($os as $i => $o) {
          if (!is_array($o)) {
            $o_val = $this->expandPName($o);
            $o_type = preg_match('/^[a-z]+\:[^\s]+$/si', $o_val) ? 'uri' : 'literal';
            $o = array('value' => $o_val, 'type' => $o_type);
          }
          $os[$i] = $o;
        }
        $r[$s][$p] = $os;
      }
    }
    return $r;
  }

  /*  */
  
  function calcURI($path, $base = "") {
    /* quick check */
    if (preg_match("/^[a-z0-9\_]+\:/i", $path)) {/* abs path or bnode */
      return $path;
    }
    if (preg_match('/^\$\{.*\}/', $path)) {/* placeholder, assume abs URI */
      return $path;
    }
    if (preg_match("/^\/\//", $path)) {/* net path, assume http */
      return 'http:' . $path;
    }
    /* other URIs */
    $base = $base ? $base : $this->base;
    $base = preg_replace('/\#.*$/', '', $base);
    if ($path === true) {/* empty (but valid) URIref via turtle parser: <> */
      return $base;
    }
    $path = preg_replace("/^\.\//", '', $path);
    $root = preg_match('/(^[a-z0-9]+\:[\/]{1,2}[^\/]+)[\/|$]/i', $base, $m) ? $m[1] : $base; /* w/o trailing slash */
    $base .= ($base == $root) ? '/' : '';
    if (preg_match('/^\//', $path)) {/* leading slash */
      return $root . $path;
    }
    if (!$path) {
      return $base;
    }
    if (preg_match('/^([\#\?])/', $path, $m)) {
      return preg_replace('/\\' .$m[1]. '.*$/', '', $base) . $path;
    }
    if (preg_match('/^(\&)(.*)$/', $path, $m)) {/* not perfect yet */
      return preg_match('/\?/', $base) ? $base . $m[1] . $m[2] : $base . '?' . $m[2];
    }
    if (preg_match("/^[a-z0-9]+\:/i", $path)) {/* abs path */
      return $path;
    }
    /* rel path: remove stuff after last slash */
    $base = substr($base, 0, strrpos($base, '/')+1);
    /* resolve ../ */
    while (preg_match('/^(\.\.\/)(.*)$/', $path, $m)) {
      $path = $m[2];
      $base = ($base == $root.'/') ? $base : preg_replace('/^(.*\/)[^\/]+\/$/', '\\1', $base);
    }
    return $base . $path;
  }
  
  /*  */
  
  function calcBase($path) {
    $r = $path;
    $r = preg_replace('/\#.*$/', '', $r);/* remove hash */
    $r = preg_replace('/^\/\//', 'http://', $r);/* net path (//), assume http */
    if (preg_match('/^[a-z0-9]+\:/', $r)) {/* scheme, abs path */
      while (preg_match('/^(.+\/)(\.\.\/.*)$/U', $r, $m)) {
        $r = $this->calcURI($m[1], $m[2]);
      }
      return $r;
    }
    return 'file://' . realpath($r);/* real path */
  }

  /*  */
  
  function toNTriples($v, $ns = '', $raw = 0) {
    ARC2::inc('NTriplesSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_NTriplesSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return (isset($v[0]) && isset($v[0]['s'])) ? $ser->getSerializedTriples($v, $raw) : $ser->getSerializedIndex($v, $raw);
  }
  
  function toTurtle($v, $ns = '', $raw = 0) {
    ARC2::inc('TurtleSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_TurtleSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return (isset($v[0]) && isset($v[0]['s'])) ? $ser->getSerializedTriples($v, $raw) : $ser->getSerializedIndex($v, $raw);
  }
  
  function toRDFXML($v, $ns = '', $raw = 0) {
    ARC2::inc('RDFXMLSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_RDFXMLSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return (isset($v[0]) && isset($v[0]['s'])) ? $ser->getSerializedTriples($v, $raw) : $ser->getSerializedIndex($v, $raw);
  }

  function toRDFJSON($v, $ns = '') {
    ARC2::inc('RDFJSONSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_RDFJSONSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return (isset($v[0]) && isset($v[0]['s'])) ? $ser->getSerializedTriples($v) : $ser->getSerializedIndex($v);
  }

  function toLegacyXML($v, $ns = '') {
    ARC2::inc('LegacyXMLSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_LegacyXMLSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return $ser->getSerializedArray($v);
  }

  function toLegacyJSON($v, $ns = '') {
    ARC2::inc('LegacyJSONSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_LegacyJSONSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return $ser->getSerializedArray($v);
  }

  function toLegacyHTML($v, $ns = '') {
    ARC2::inc('LegacyHTMLSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_LegacyHTMLSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return $ser->getSerializedArray($v);
  }

  function toHTML($v, $ns = '') {
    ARC2::inc('POSHRDFSerializer');
    if (!$ns) $ns = isset($this->a['ns']) ? $this->a['ns'] : array();
    $ser = new ARC2_POSHRDFSerializer(array_merge($this->a, array('ns' => $ns)), $this);
    return (isset($v[0]) && isset($v[0]['s'])) ? $ser->getSerializedTriples($v) : $ser->getSerializedIndex($v);
  }

  /*  */

  function getFilledTemplate($t, $vals, $g = '') {
    $parser = ARC2::getTurtleParser();
    $parser->parse($g, $this->getTurtleHead() . $t);
    return $parser->getSimpleIndex(0, $vals);
  }
  
  function getTurtleHead() {
    $r = '';
    $ns = $this->v('ns', array(), $this->a);
    foreach ($ns as $k => $v) {
      $r .= "@prefix " . $k . ": <" .$v. "> .\n";
    }
    return $r;
  }

  /*  */

  function toUTF8($v) {
    return $this->adjust_utf8 ? ARC2::toUTF8($v) : $v;
  }

  /*  */
  
}
