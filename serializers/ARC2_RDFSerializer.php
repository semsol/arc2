<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 RDF Serializer
author:   Benjamin Nowack
version:  2008-09-11 (Addition: "raw" parameter)
*/

ARC2::inc('Class');

class ARC2_RDFSerializer extends ARC2_Class {

  function __construct($a = '', &$caller) {
    parent::__construct($a, $caller);
  }
  
  function ARC2_RDFSerializer($a = '', &$caller) {/* ns */
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->ns_count = 0;
    $this->ns = $this->v('ns', array(), $this->a);
    $this->nsp = array();
    foreach ($this->ns as $k => $v) {
      $this->nsp[$v] = $k;
    }
    $this->nsp['http://www.w3.org/1999/02/22-rdf-syntax-ns#'] = 'rdf';
    $this->used_ns = array('http://www.w3.org/1999/02/22-rdf-syntax-ns#');
  }

  /*  */
  
  function getPrefix($ns) {
    if (!isset($this->nsp[$ns])) {
      $this->ns['ns' . $this->ns_count] = $ns;
      $this->nsp[$ns] = 'ns' . $this->ns_count;
      $this->ns_count++;
    }
    $this->used_ns = !in_array($ns, $this->used_ns) ? array_merge($this->used_ns, array($ns)) : $this->used_ns;
    return $this->nsp[$ns];
  }

  function getPName($v) {
    if (preg_match('/^([a-z0-9\_\-]+)\:([a-z\_][a-z0-9\_\-]*)$/i', $v, $m) && isset($this->ns[$m[1]])) {
      $this->used_ns = !in_array($this->ns[$m[1]], $this->used_ns) ? array_merge($this->used_ns, array($this->ns[$m[1]])) : $this->used_ns;
      return $v;
    }
    if (preg_match('/^(.*[\/\#])([a-z\_][a-z0-9\-\_]*)$/i', $v, $m)) {
      return $this->getPrefix($m[1]) . ':' . $m[2];
    }
    return 0;
  }
  
  /*  */
  
  function getSerializedTriples($triples, $raw = 0) {
    $index = ARC2::getSimpleIndex($triples, 0);
    return $this->getSerializedIndex($index, $raw);
  }
  
  function getSerializedIndex() {
    return '';
  }
  
  /*  */
  
}
