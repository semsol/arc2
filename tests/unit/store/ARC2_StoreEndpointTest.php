<?php

namespace Tests\unit\store;

use Tests\ARC2_TestCase;


//Tests ARC2_StoreEndpoint functions  
class ARC2_StoreEndpointTest extends ARC2_TestCase
{
    
    public function setUp(): void
    {
        parent::setUp();
        $this->endpoint = \ARC2::getStoreEndpoint($this->dbConfig);
        $this->endpoint->createDBCon();
    }
    public function testJSON()
    {
        
        $data = array(
            'result' => array(
                'variables' => array(
                    'a',
                    'b',
                    'c'
                ),
                'rows' => array(
                    array(
                        'a' => 'http://de.dbpedia.org/resource/Johann_von_Pont',
                        'a type' => 'uri',
                        'b' => 'http://dbpedia.org/ontology/deathPlace',
                        'b type' => 'uri',
                        'c' => 'http://de.dbpedia.org/resource/Aachen',
                        'c type' => 'uri'
                    ),
                    array(
                        'a' => 'http://de.dbpedia.org/resource/Aachen',
                        'a type' => 'uri',
                        'b' => 'http://dbpedia.org/ontology/elevation',
                        'b type' => 'uri',
                        'c' => '173.0',
                        'c type' => 'literal',
                        'c datatype' => 'http://www.w3.org/2001/XMLSchema#double'
                    ),
                    array(
                        'a' => 'http://de.dbpedia.org/resource/Aachen',
                        'a type' => 'uri',
                        'b' => 'http://dbpedia.org/ontology/leaderTitle',
                        'b type' => 'uri',
                        'c' => 'Oberbürgermeister',
                        'c type' => 'literal',
                        'c lang' => 'de'
                    )
                )
            ),
            'query_time' => 1
        );
        $res  = json_decode($this->endpoint->getSPARQLJSONSelectResultDoc($data), true);
        $this->assertArrayHasKey('head', $res);
        $this->assertArrayHasKey('results', $res);
        $this->assertEquals($res['head']['vars'][0], 'a');
        $this->assertEquals($res['results']['bindings'][0]['a']['value'], 'http://de.dbpedia.org/resource/Johann_von_Pont');
        $this->assertEquals($res['results']['bindings'][1]['c']['type'], 'literal');
        $this->assertEquals($res['results']['bindings'][2]['c']['type'], 'literal');
    }
}
