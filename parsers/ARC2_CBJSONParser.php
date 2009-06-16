<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 CrunchBase API JSON Parser
author:   Benjamin Nowack
version:  2008-08-06 (Tweak: Removed inferred "full_permalink", there is a native "crunchbase_url" now)
*/

ARC2::inc('JSONParser');

class ARC2_CBJSONParser extends ARC2_JSONParser {

  function __construct($a = '', &$caller) {
    parent::__construct($a, $caller);
  }
  
  function ARC2_CBJSONParser($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {/* reader */
    parent::__init();
    $this->base = 'http://cb.semsol.org/';
    $this->rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    $this->default_ns = $this->base . 'ns#';
    $this->nsp = array($this->rdf => 'rdf');
  }
  
  /*  */

  function done() {
    $this->extractRDF();
  }
  
  function extractRDF() {
    $struct = $this->struct;
    if ($type = $this->getStructType($struct)) {
      $s = $this->getResourceID($struct, $type);
      /* rdf:type */
      $this->addT($s, $this->rdf . 'type', $this->default_ns . $this->camelCase($type), 'uri', 'uri');
      /* explicit triples */
      $this->extractResourceRDF($struct, $s);
    }
    //print_r($struct);
  }
  
  function getStructType($struct, $rel = '') {
    /* rel-based */
    if ($rel == 'person') return 'person';
    if ($rel == 'company') return 'company';
    if ($rel == 'acquiring_company') return 'company';
    if ($rel == 'firm') return 'company';
    if ($rel == 'provider') return 'service-provider';
    /* struct-based */
    if (isset($struct['_type'])) return $struct['_type'];
    if (isset($struct['round_code'])) return 'funding_round';
    if (isset($struct['products'])) return 'company';
    if (isset($struct['first_name'])) return 'person';
    if (isset($struct['investments'])) return 'financial-organization';
    if (isset($struct['launched_year'])) return 'product';
    if (isset($struct['providerships']) && is_array($struct['providerships'])) return 'service-provider';
    return '';
  }
  
  function getResourceID($struct, $type) {
    if ($type && isset($struct['permalink'])) return $this->base . $type . '/' . $struct['permalink'] . '#self';
    return $this->createBnodeID();
  }
  
  function getPropertyURI($name, $ns = '') {
    if (!$ns) $ns = $this->default_ns;
    if (preg_match('/^(product|funding_round|investment|acquisition|.+ship|office|milestone|.+embed|.+link)s/', $name, $m)) $name = $m[1];
    if ($name == 'tag_list') $name = 'tag';
    if ($name == 'competitions') $name = 'competitor';
    return $ns . $name;
  }
  
  /*  */
  
  function extractResourceRDF($struct, $s) {
    $s_type = preg_match('/^\_\:/', $s) ? 'bnode' : 'uri';
    foreach ($struct as $k => $v) {
      if ($k == 'acquisition') $k = 'exit';
      $sub_m = 'extract' . $this->camelCase($k) . 'RDF';
      if (method_exists($this, $sub_m)) {
        $this->$sub_m($s, $s_type, $v);
        continue;
      }
      $p = $this->getPropertyURI($k);
      if (!$v) continue;
      /* simple, single v */
      if (!is_array($v)) {
        $o_type = preg_match('/^[a-z]+\:[^\s]+$/is', $v) ? 'uri' : 'literal';
        $this->addT($s, $p, trim($v), $s_type, $o_type);
      }
      /* structured, single v */
      elseif (!$this->isFlatArray($v)) {
        if ($o_type = $this->getStructType($v, $k)) {/* known type */
          $o = $this->getResourceID($v, $o_type);
          $this->addT($s, $p, $o, $s_type, 'uri');
          $this->addT($o, $this->rdf . 'type', $this->default_ns . $this->camelCase($o_type), 'uri', 'uri');
        }
        else {/* unknown type */
          $o = $this->createBnodeID();
          $this->addT($s, $p, $o, $s_type, 'bnode');
          $this->extractResourceRDF($v, $o);
        }
      }
      /* value list */
      else {
        foreach ($v as $sub_v) {
          $this->extractResourceRDF(array($k => $sub_v), $s);
        }
      }
    }
  }

  function isFlatArray($v) {
    foreach ($v as $k => $sub_v) {
      return is_numeric($k) ? 1 : 0;
    }
  }
  
  /*  */
  
  function extractTagListRDF($s, $s_type,  $v) {
    $tags = split(', ', $v);
    foreach ($tags as $tag) {
      if (!trim($tag)) continue;
      $this->addT($s, $this->getPropertyURI('tag'), $tag, $s_type, 'literal');
    }
  }

  function extractImageRDF($s, $s_type, $v) {
    if (!$v) return 1;
    $sizes = $v['available_sizes'];
    foreach ($sizes as $size) {
      $w = $size[0][0];
      $h = $size[0][1];
      $img = 'http://www.crunchbase.com/' . $size[1];
      $this->addT($s, $this->getPropertyURI('image'), $img, $s_type, 'uri');
      $this->addT($img, $this->getPropertyURI('width'), $w, 'uri', 'literal');
      $this->addT($img, $this->getPropertyURI('height'), $h, 'uri', 'literal');
    }
  }
  
  function extractProductsRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      $o = $this->getResourceID($sub_v, 'product');
      $this->addT($s, $this->getPropertyURI('product'), $o, $s_type, 'uri');
    }
  }

  function extractCompetitionsRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      $o = $this->getResourceID($sub_v['competitor'], 'company');
      $this->addT($s, $this->getPropertyURI('competitor'), $o, $s_type, 'uri');
    }
  }

  function extractFundingRoundsRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      $o = $this->createBnodeID();
      $this->addT($s, $this->getPropertyURI('funding_round'), $o, $s_type, 'bnode');
      $this->extractResourceRDF($sub_v, $o);
    }
  }

  function extractInvestmentsRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      /* incoming */
      foreach (array('person' => 'person', 'company' => 'company', 'financial_org' => 'financial-organization') as $k => $type) {
        if (isset($sub_v[$k])) $this->addT($s, $this->getPropertyURI('investment'), $this->getResourceID($sub_v[$k], $type), $s_type, 'uri');
      }
      /* outgoing */
      if (isset($sub_v['funding_round'])) {
        $o = $this->createBnodeID();
        $this->addT($s, $this->getPropertyURI('investment'), $o, $s_type, 'bnode');
        $this->extractResourceRDF($sub_v['funding_round'], $o);
      }
    }
  }
  
  function extractExternalLinksRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      $this->addT($s, $this->getPropertyURI('external_link'), $sub_v['external_url'], $s_type, 'uri');
      $this->addT($sub_v['external_url'], $this->getPropertyURI('title'), $sub_v['title'], $s_type, 'literal');
    }
  }

  function extractWebPresencesRDF($s, $s_type, $v) {
    foreach ($v as $sub_v) {
      $this->addT($s, $this->getPropertyURI('web_presence'), $sub_v['external_url'], $s_type, 'uri');
      $this->addT($sub_v['external_url'], $this->getPropertyURI('title'), $sub_v['title'], $s_type, 'literal');
    }
  }

  /*  */
  
}
