<?php
/**
 * ARC2 Graph object
 *
 * @author Benjamin Nowack <mail@bnowack.de>
 * @license W3C Software License
 * @homepage <http://arc.semsol.org/>
 * @package ARC2
*/

ARC2::inc('Class');

class ARC2_Graph extends ARC2_Class {
	
	protected $index;

	function __construct($a, &$caller) {
		parent::__construct($a, $caller);
	}
  
	function __init() {
		parent::__init();
		$this->index = array();
	}
  
	function setIndex($index) {
		$this->index = $index;
		return $this;
	}

	function getIndex() {
		return $this->index;
	}
	
	function addIndex($index) {
		$this->index = ARC2::getMergedIndex($this->index, $index);
		return $this;
	}
	
	function addGraph($graph) {
		// namespaces
		foreach ($graph->ns as $prefix => $ns) {
			$this->setPrefix($prefix, $ns);
		}
		// index
		$this->addIndex($graph->getIndex());
		return $this;
	}
	
	function addRdf($data, $format = null) {
		if ($format == 'json') {
			return $this->addIndex(json_decode($data, true));
		}
		else {// parse any other rdf format
			return $this->addIndex($this->toIndex($data));
		}
	}
	
	function hasSubject($s) {
		return isset($this->index[$s]);
	}
		
	function hasTriple($s, $p, $o) {
		if (!is_array($o)) {
			return $this->hasLiteralTriple($s, $p, $o) || $this->hasLinkTriple($s, $p, $o);
		}
		if (!isset($this->index[$s])) return false;
		$p = $this->expandPName($p);
		if (!isset($this->index[$s][$p])) return false;
		return in_array($o, $this->index[$s][$p]);
	}
	
	function hasLiteralTriple($s, $p, $o) {
		if (!isset($this->index[$s])) return false;
		$p = $this->expandPName($p);
		if (!isset($this->index[$s][$p])) return false;
		$os = $this->getObjects($s, $p, false);
		foreach ($os as $object) {
			if ($object['value'] == $o && $object['type'] == 'literal') {
				return true;
			}
		}
		return false;
	}
  
	function hasLinkTriple($s, $p, $o) {
		if (!isset($this->index[$s])) return false;
		$p = $this->expandPName($p);
		if (!isset($this->index[$s][$p])) return false;
		$os = $this->getObjects($s, $p, false);
		foreach ($os as $object) {
			if ($object['value'] == $o && ($object['type'] == 'uri' || $object['type'] == 'bnode')) {
				return true;
			}
		}
		return false;
	}
  
	function addTriple($s, $p, $o, $oType = 'literal') {
		$p = $this->expandPName($p);
		if (!is_array($o)) $o = array('value' => $o, 'type' => $oType);
		if ($this->hasTriple($s, $p, $o)) return;
		if (!isset($this->index[$s])) $this->index[$s] = array();
		if (!isset($this->index[$s][$p])) $this->index[$s][$p] = array();
		$this->index[$s][$p][] = $o;
		return $this;
	}
	
	function getSubjects($p = null, $o = null) {
		if (!$p && !$o) return array_keys($this->index);
		$result = array();
		foreach ($this->index as $s => $ps) {
			foreach ($ps as $predicate => $os) {
				if ($p && $predicate != $p) continue;
				foreach ($os as $object) {
					if (!$o) {
						$result[] = $s;
						break;
					}
					else if (is_array($o) && $object == $o) {
						$result[] = $s;
						break;
					}
					else if ($o && $object['value'] == $o) {
						$result[] = $s;
						break;
					}
				}
			}
		}
		return array_unique($result);
	}
	
	function getPredicates($s = null) {
		$result = array();
		$index = $s ? (array($s => isset($this->index[$s]) ? $this->index[$s] : array())) : $this->index;
		foreach ($index as $subject => $ps) {
			if ($s && $s != $subject) continue;
			$result = array_merge($result, array_keys($ps));
		}
		return array_unique($result);
	}
	
	function getObjects($s, $p, $plain = false) {
		if (!isset($this->index[$s])) return array();
		$p = $this->expandPName($p);
		if (!isset($this->index[$s][$p])) return array();
		$os = $this->index[$s][$p];
		if ($plain) {
			array_walk($os, function(&$o) {
				$o = $o['value'];
			});
		}
		return $os;
	}
	
	function getObject($s, $p, $plain = false, $default = null) {
		$os = $this->getObjects($s, $p, $plain);
		return empty($os) ? $default : $os[0];
	}

	function getNTriples() {
		return parent::toNTriples($this->index, $this->ns);
	}

	function getTurtle() {
		return parent::toTurtle($this->index, $this->ns);
	}

	function getRDFXML() {
		return parent::toRDFXML($this->index, $this->ns);
	}

	function getJSON() {
		return json_encode($this->index);
	}
	
}
