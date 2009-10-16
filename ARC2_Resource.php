<?php
/**
 * ARC2 Resource object
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @license http://arc.semsol.org/license
 * @package ARC2
 * @version 2009-10-16
*/

ARC2::inc('Class');

class ARC2_Resource extends ARC2_Class {

  function __construct($a = '', &$caller) {
    parent::__construct($a, $caller);
  }
  
  function ARC2_Resource($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->uri = '';
    $this->index = array();
    $this->fetched = array();
    $this->store = '';
  }

  /*  */
  
  function setURI($uri) {
    $this->uri = $uri;
  }

  function setIndex($index) {
    $this->index = $index;
  }

  function setProps($props, $s = '') {
    if (!$s) $s = $this->uri;
    $this->index[$s] = $props;
  }

  function setStore($store) {
    $this->store = $store;
  }

  /*  */

  function fetchData($uri = '') {
    if (!$uri) $uri = $this->uri;
    if (!$uri) return 0;
    if (in_array($uri, $this->fetched)) return 0;
    $this->index[$uri] = array();
    if ($this->store) {
      $index = $this->store->query('DESCRIBE <' . $uri . '>', 'raw');
    }
    else {
      $index = $this->toIndex($uri);
    }
    $this->index = ARC2::getMergedIndex($this->index, $index);
    $this->fetched[] = $uri;
  }

  /*  */
  
  function getProps($p = '', $s = '') {
    if (!$s) $s = $this->uri;
    if (!$s) return array();
    if (!isset($this->index[$s])) $this->fetchData($s);
    if (!$p) return $this->index[$s];
    return $this->v($this->expandPName($p), array(), $this->index[$s]);
  }

  function getProp($p, $s = '') {
    $props = $this->getProps($p, $s);
    return $props ? $props[0] : '';
  }

  function getPropValue($p, $s = '') {
    $prop = $this->getProp($p, $s);
    return $prop ? $prop['value'] : '';
  }

  function getPropValues($p, $s = '') {
    $r = array();
    $props = $this->getProps($p, $s);
    foreach ($props as $prop) {
      $r[] = $prop['value'];
    }
    return $r;
  }

  function hasPropValue($p, $o, $s = '') {
    $props = $this->getProps($p, $s);
    $o = $this->expandPName($o);
    foreach ($props as $prop) {
      if ($prop['value'] == $o) return 1;
    }
    return 0;
  }

  /*  */

}
