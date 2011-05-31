<?php

/* ARC2 static class inclusion */ 
include_once('../path/to/ARC2.php');

/* MySQL and endpoint configuration */ 
$config = array(
  /* db */
  'db_host' => 'localhost', /* optional, default is localhost */
  'db_name' => 'database',
  'db_user' => 'dbuser',
  'db_pwd' => 'dbpw',
          
  /* store name */
  'store_name' => 'storename',
);

$store = ARC2::getStoreEndpoint($config);

if (!$store->isSetUp()) {
  $store->setUp();
}

$json = json_encode(array());

$comp = ARC2::getComponent('ARC2_JITSerializerPlugin', $config);

if(!empty($_GET)) {
  if(!empty($_GET["query"])) {
    $query = $_GET["query"];
  } else {
    $query = "SELECT * WHERE {  GRAPH ?g { ?s ?p ?o . } } LIMIT 10";
  }
} else {
  $query = "SELECT * WHERE {  GRAPH ?g { ?s ?p ?o . } } LIMIT 10";
}
$triples = $store->query($query, "rows");  

$json = $comp->getJitJson($triples);

$q = $store->p('query') ? htmlspecialchars($store->p('query')) : "SELECT * WHERE {\n  GRAPH ?g { ?s ?p ?o . }\n}\nLIMIT 10";

echo '
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>RGraph - Tree Animation</title>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8"/>
    <meta http-equiv="Content-Script-Type" content="text/javascript"/>
    <link type="text/css" href="screen.css" rel="stylesheet" />
    <script type="text/javascript" src="Jit/jit.js"></script>
    <script type="text/javascript" src="jquery-1.6.1.js"></script>
    <script type="text/javascript" src="jitendpoint.js"></script>
</head>
<body onload="init(' . htmlentities($json) .');">
<div id="wrapper">
    <div id="header">
        <a href="http://wiss-ki.eu"><img id="logo" src="wisski_logo.png" alt="WissKI"/></a>
        <h1>ARC2 JIT Graph Visualization</h1>
    </div>   
    <div id="col-left">
        <h2>Visualize your query</h2>
        <p>This interface is based on <a href="https://github.com/semsol/arc2">ARC2</a> and the SPARQL endpoint implementing <a href="http://www.w3.org/TR/rdf-sparql-query/">SPARQL</a> and <a href="http://arc.semsol.org/docs/v2/sparql+">SPARQL+</a> via <a href="http://www.w3.org/TR/rdf-sparql-protocol/#query-bindings-http">HTTP Bindings</a> and visualizes the results with the <a href="http://thejit.org">Javascript Infovis Toolkit</a>. Currently querys must have the variables ?s ?p and ?o and only querys via GET are supported. <br>More examples including dynamic node loading in large triple stores can be seen at <a href="http://wiss-ki.eu">WissKI</a>. </p>
        <h2>Query Form</h2>
        <form id="sparql-form" action="?" enctype="application/x-www-form-urlencoded" method="get">
            <fieldset>
                <textarea id="query" name="query" rows="20" cols="80">' . $q . '</textarea>
                <input type="submit" value="Send Query" />
                <input type="reset" value="Reset" />
            </fieldset>
        </form>
    </div>
    <div id="col-right">
        <div id="inner-details"></div>
    </div>
    <div id="col-center">
        <h2>RGraph Visualization</h2>
        <div id="infovis"></div>    
    </div>
    <div id="log"></div>
    <div id="footer"><a href="mailto:m.fichtner@wiss-ki.eu">Mark Fichtner,</a> <a href="mailto:g.hohmann@wiss-ki.eu">Georg Hohmann</a> <a href="http://wiss-ki.eu">WissKI 2011</a></div>
</div>
</body>
</html>';

