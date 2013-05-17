<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 Legacy JSON Serializer
author:   Benjamin Nowack
version:  2010-11-16
*/

ARC2::inc('Class');

class ARC2_LegacyJSONSerializer extends ARC2_Class {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
    $this->content_header = 'application/json';
  }

  /*  */
  
  function getSerializedArray($struct, $ind = '') {
    $n = "\n";
    if (function_exists('json_encode')) return str_replace('","', '",' . $n . '"', str_replace("\/","/",json_encode($struct)));
    $r = '';
    $from = array("\\", "\r", "\t", "\n", '"', "\b", "\f");
    $to = array('\\\\', '\r', '\t', '\n', '\"', '\b', '\f');
    $is_flat = $this->isAssociativeArray($struct) ? 0 : 1;
    foreach ($struct as $k => $v) {
      $r .= $r ? ',' . $n . $ind . $ind : $ind . $ind;
      $r .= $is_flat ? '' : '"' . $k . '": ';
      $r .= is_array($v) ? $this->getSerializedArray($v, $ind . '  ') : '"' . str_replace($from, $to, $v) . '"';
    }
    return $is_flat ? $ind . '[' . $n . $r . $n . $ind . ']' : $ind . '{' . $n . $r . $n . $ind . '}';
  }
  
  /*  */

  function isAssociativeArray($v) {
    foreach (array_keys($v) as $k => $val) {
      if ($k !== $val) return 1;
    }
    return 0;
  }
  
  /*  */

}

