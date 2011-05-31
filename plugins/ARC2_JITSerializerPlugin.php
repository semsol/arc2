<?php
/*
homepage: http://wiss-ki.eu/
license:  http://arc.semsol.org/license

class:    ARC2 Jit Triple Visualizer Plugin
author:   Mark Fichtner
version:  2011-05-25
*/

ARC2::inc('Class');

class ARC2_JITSerializerPlugin extends ARC2_Class {
  function __construct ($a = '', &$caller) {
    parent::__construct($a, $caller);
  }
     
  function ARC2_TriplesVisualizerPlugin ($a = '', &$caller) {
    $this->__construct($a, $caller);
  }
                  
  function __init () {
    parent::__init();
  }
  
  function getJitJson($triples) {
    if (ARC2::getStructType($triples) != 'triples') {
      return $this->addError('Input structure is not a triples array.');
    };

    if(empty($triples)) {
      return json_encode(array());
    };
    
    $nodes = array();
    
    foreach ($triples as $i=>$t) {
      if(!array_key_exists($t['s'],$nodes)) {
        $nodes[$t['s']]['id'] = $t['s'];
        $nodes[$t['s']]['name'] = $t['s'];
        $nodes[$t['s']]['children'] = array();
        $nodes[$t['s']]['data']['relation'] = "<h2>Connections</h2><ul>";
      }
      
      $nodes[$t['s']]['data']['relation'] = $nodes[$t['s']]['data']['relation'] . "<li> out: " . $t['o'] . " ( " . $t['p'] . " )</li>";
      $nodes[$t['s']]['children'][$t['o']] = true;
      
      if(!array_key_exists($t['o'],$nodes)) {
        $nodes[$t['o']]['id'] = $t['o'];
        $nodes[$t['o']]['name'] = $t['o'];
        $nodes[$t['o']]['children'] = array();
        $nodes[$t['o']]['data']['relation'] = "<h2>Connections</h2><ul>";
      }

      $nodes[$t['o']]['data']['relation'] = $nodes[$t['o']]['data']['relation'] . "<li> in: " . $t['s'] . " ( " . $t['p'] . " )</li>";
      $nodes[$t['o']]['children'][$t['s']] = true;      
    }
    
    $out = array();
    $done = array();
    
    foreach ($nodes as $node) {
      $node['data']['relation'] .= "</ul>";
    }
    

    foreach ($nodes as $key => $info) {
      if(!in_array($key, $done)) {
        $tmp = $this->recursiveMergeChildren($key, $nodes, $done);
        $out = &$tmp;
      }
    }
    return json_encode($out);
  }
  
  function recursiveMergeChildren($key, $nodes, &$done) {
    if(!in_array($key, $done)) {
      $obj = $nodes[$key];
      $done[] = $key;
      $objchildren = array();
      foreach($obj['children'] as $childkey => $true) {
        $objchildren[] = $this->recursiveMergeChildren($childkey, $nodes, $done);
      }
      $obj['children'] = &$objchildren;
      return $obj;
    }
    $obj = $nodes[$key];
    unset($obj['children']);
    return $obj;
  }
  
}

  
  