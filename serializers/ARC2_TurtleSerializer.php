<?php
/*
    Changed the string escaping - turtle can't have single quotes.
    Outputs "singleton bnodes" inline with [].
    Serialises rdf:Lists as () where possible.
    Not properly tested  ....
*/
/**
 * ARC2 Turtle Serializer
 *
 * @author    Benjamin Nowack
 * @edited    Keith Alexander
 * @license   http://arc.semsol.org/license
 * @homepage <http://arc.semsol.org/>
 * @package   ARC2
 * @version   2010-12-24
*/

ARC2::inc('RDFSerializer');

class ARC2_TurtleSerializer extends ARC2_RDFSerializer {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
    $this->content_header = 'text/turtle';
  }

  function occurrencesOfIdAsObject($id, &$index) {
      $count = 0;
      foreach($index as $s => $ps){
          foreach($ps as $p => $os){
              if(in_array(array('value'=>$id,'type'=>'bnode'), $os) ) $count++;
          }
      }
      return $count;
  }
  
  function resourceIsRdfList($id, &$index){
      $rdftype = $this->expandPName('rdf:type');
      $rdfList = $this->expandPName('rdf:List');
      $rdffirst = $this->expandPName('rdf:first');
      if(isset($index[$id][$rdffirst])) return true;
       if(isset($index[$id]) && isset($index[$id][$rdftype])){
           $types = $index[$id][$rdftype];
           return in_array(array('value' => $rdfList, 'type'=> 'uri'), $types);
       }
       return null;
  }

  function listToHash($listID, &$index){
      $array = array();
            $rdffirst = $this->expandPName('rdf:first');
            $rdfrest = $this->expandPName('rdf:rest');
            $rdfnil = $this->expandPName('rdf:nil');
      while(!empty($listID) AND $listID !=$rdfnil){
          $array[$listID]=$index[$listID][$rdffirst][0];
          $listID = $index[$listID][$rdfrest][0]['value'];
      }
      return $array;
  }
  

  /*  */
  
  function getTerm($v, $term = '', $qualifier = '', &$index=array()) {
    if (!is_array($v)) {
      if (preg_match('/^\_\:/', $v)) {
        $objectCount =$this->occurrencesOfIdAsObject($v, $index);  
        if($objectCount<2){ //singleton bnode 
             return '[';  /* getSerializedIndex will fill in the  ] at the end */
        } else {
            return $v;
        }
      }
      if (($term === 'p') && ($pn = $this->getPName($v))) {
        return $pn;
      }
      if (
        ($term === 'o') &&
        in_array($qualifier, array('rdf:type', 'rdfs:domain', 'rdfs:range', 'rdfs:subClassOf')) &&
        ($pn = $this->getPName($v))
      ) {
        return $pn;
      }
      if (preg_match('/^[a-z0-9]+\:[^\s]*$/is' . ($this->has_pcre_unicode ? 'u' : ''), $v)) {
        return '<' .$v. '>';
      }
      return $this->getTerm(array('type' => 'literal', 'value' => $v), $term, $qualifier);
    }
    if (!isset($v['type']) || ($v['type'] != 'literal')) {
      return $this->getTerm($v['value'], $term, $qualifier);
    }
    /* literal */
    $quot = '"';        
      if (preg_match('/\"/', $v['value']) || preg_match('/[\x0d\x0a]/', $v['value'])) {
        $quot = '"""';
        if (preg_match('/\"\"\"/', $v['value']) || preg_match('/\"$/', $v['value']) || preg_match('/^\"/', $v['value'])) {
          $quot = "'''";
          $v['value'] = preg_replace("/'$/", "' ", $v['value']);
          $v['value'] = preg_replace("/^'/", " '", $v['value']);
          $v['value'] = str_replace("'''", '\\\'\\\'\\\'', $v['value']);
        }
      }
    if ((strlen($quot) == 1) && preg_match('/[\x0d\x0a]/', $v['value'])) {
      $quot = $quot . $quot . $quot;
    }
    $suffix = isset($v['lang']) && $v['lang'] ? '@' . $v['lang'] : '';
    $suffix = isset($v['datatype']) && $v['datatype'] ? '^^' . $this->getTerm($v['datatype'], 'dt') : $suffix;
    return $quot . $v['value'] . $quot . $suffix;
  }
  
  function getHead() {
    $r = '';
    $nl = "\n";
    foreach ($this->used_ns as $v) {
      $r .= $r ? $nl : '';
      foreach ($this->ns as $prefix => $ns) {
        if ($ns != $v) continue;
        $r .= '@prefix ' . $prefix . ': <' .$v. '> .';
        break;
      }
    }
    return $r;
  }
  
  function getSerializedIndex($index, $raw = 0) {
    $r = '';
    $nl = "\n";
    $renderedResources = array();
    foreach ($index as $s => $ps) {
        $renderedResources[$s] = $this->_serialiseResource($s, $index,$nl);
    }
    $topLevelSubjects = array_keys($index);
    foreach($renderedResources as $id => $turtle){
        if(!in_array($id, $topLevelSubjects)) unset($renderedResources[$id]);
    }
    $r.= implode(array_values($renderedResources));
    if ($raw) {
      return $r;
    }
    return $r ? $this->getHead() . $nl . $nl . $r : '';
  }
  
  function _serialiseResource($s, &$index, $nesting=0, $nl="\n"){
      $r='';
      if(!isset($index[$s])) return $r;
      else $ps = $index[$s];
      $r .= $r ? ' .' . $nl . $nl : '';
      $s = $this->getTerm($s, 's');
      $r .= $s;
      $first_p = 1;
      foreach ($ps as $p => $os) {
        if (!$os) continue;
        $p = $this->getTerm($p, 'p');
        $r .= $first_p ? ' ' : ' ;' . $nl . str_pad('', strlen($s) + 1);
        $r .= $p;
        $first_o = 1;
        if (!is_array($os)) {/* single literal o */
          $os = array(array('value' => $os, 'type' => 'literal'));
        }
        foreach ($os as $o) {
          $r .= $first_o ? ' ' : ' ,' . $nl . str_pad('', strlen($s) + strlen($p) + 2);
          $termO = $this->getTerm($o, 'o', $p, $index);
          if($termO=='['){ // we know it's a singleton bnode
              $termID = isset($o['value'])? $o['value'] : $o;
              if($this->resourceIsRdfList($termID, $index)){
                  $renderAsList = true;
                $list = $this->listToHash($termID, $index);
                $listText= '( ';
                foreach ($list as $listID => $listValue) {
                    if($this->occurrencesOfIdAsObject($listID, $index) < 2 ){
                      $listText.=$this->getTerm($listValue, 'o', null, $index).' ';  
                    } 
                    else {
                        $renderAsList = false;
                    }
                }
                $listText.=')';
                if($renderAsList){ 
                    $r.=$listText;
                    foreach($list as $listID => $listValue) unset($index[$listID]);
                } else {
                    $r.=$this->_serialiseResource($termID, $index, ($nesting+1));
                } 
              } else {
                $r.=$this->_serialiseResource($termID, $index, ($nesting+1));
              }
              unset($index[$termID]);
          } else {
              $r .= $termO;
          }
          $first_o = 0;
        }
        $first_p = 0;
      }
      if($s=='[') $r.=']';
      $r .= $r && ($nesting < 1) ? ' . ' : '';
      
      return $r.$nl.$nl;
  }
  
  /*  */

}
?>
