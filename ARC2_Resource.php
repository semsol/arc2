<?php
/**
 * ARC2 Resource object
 *
 * @version 2009-08-17
 *
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
  }

  /*  */
  
  function setURI($uri) {
    $this->uri = $uri;
  }

  function setProps($props, $s = '') {
    if (!$s) $s = $this->uri;
    $this->index[$s] = $props;
  }

  /*  */

  function fetchData($uri) {
    if (!$uri) return 0;
    if (in_array($uri, $this->fetched)) return 0;
    $this->index[$uri] = array();
    $this->index = ARC2::getMergedIndex($this->index, $this->toIndex($uri));
    $this->fetched[] = $uri;
  }

  /*  */
  
  function getProps($p, $s = '') {
    if (!$s) $s = $this->uri;
    if (!$s) return array();
    if (!isset($this->index[$s])) $this->fetchData($s);
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
