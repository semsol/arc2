<?php

namespace Tests\integration\store\query;

use Tests\ARC2_TestCase;

/**
 * Tests for query method - focus on SELECT queries
 */
class SelectQueryTest extends ARC2_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = \ARC2::getStore($this->dbConfig);
        $this->fixture->drop();
        $this->fixture->setup();
    }

    public function testSelectDefaultGraph()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
        }');

        $res = $this->fixture->query('SELECT * WHERE {<http://s> <http://p1> ?o.}');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'o'
                    ],
                    'rows' => [
                        [
                            'o' => 'baz',
                            'o type' => 'literal'
                        ]
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectGraphSpecified()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://s> <http://p1> "baz" .
        }');

        $res = $this->fixture->query('SELECT * FROM <http://example.com/> {<http://s> <http://p1> ?o.}');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'o'
                    ],
                    'rows' => [
                        [
                            'o' => 'baz',
                            'o type' => 'literal'
                        ]
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    /*
     * SELECT COUNT
     */

    public function testSelectCount()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://name> "baz" .
            <http://person2> <http://name> "baz" .
            <http://person3> <http://name> "baz" .
        }');

        $res = $this->fixture->query('
            SELECT COUNT(?s) AS ?count WHERE {
                ?s <http://name> "baz" .
            }
            ORDER BY DESC(?count)
        ');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'count'
                    ],
                    'rows' => [
                        [
                            'count' => '3',
                            'count type' => 'literal',
                        ]
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    /*
     * GROUP BY
     */

    public function testSelectGroupBy()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://knows> <http://person2>, <http://person3> .
            <http://person2> <http://knows> <http://person3> .
        }');

        $res = $this->fixture->query('
            SELECT ?who COUNT(?person) as ?persons WHERE {
                ?who <http://knows> ?person .
            }
            GROUP BY ?who
        ');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'who',
                        'persons'
                    ],
                    'rows' => [
                        [
                            'who' => 'http://person1',
                            'who type' => 'uri',
                            'persons' => '2',
                            'persons type' => 'literal',
                        ],
                        [
                            'who' => 'http://person2',
                            'who type' => 'uri',
                            'persons' => '1',
                            'persons type' => 'literal',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    /*
     * ORDER BY
     */

    public function testSelectOrderByAsc()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://id> "1" .
            <http://person3> <http://id> "3" .
            <http://person2> <http://id> "2" .
        }');

        $res = $this->fixture->query('
            SELECT * WHERE {
                ?s <http://id> ?id .
            }
            ORDER BY ASC(?id)
        ');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        's',
                        'id'
                    ],
                    'rows' => [
                        [
                            's' => 'http://person1',
                            's type' => 'uri',
                            'id' => '1',
                            'id type' => 'literal',
                        ],
                        [
                            's' => 'http://person2',
                            's type' => 'uri',
                            'id' => '2',
                            'id type' => 'literal',
                        ],
                        [
                            's' => 'http://person3',
                            's type' => 'uri',
                            'id' => '3',
                            'id type' => 'literal',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectOrderByDesc()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://id> "1" .
            <http://person3> <http://id> "3" .
            <http://person2> <http://id> "2" .
        }');

        $res = $this->fixture->query('
            SELECT * WHERE {
                ?s <http://id> ?id .
            }
            ORDER BY DESC(?id)
        ');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        's',
                        'id'
                    ],
                    'rows' => [
                        [
                            's' => 'http://person3',
                            's type' => 'uri',
                            'id' => '3',
                            'id type' => 'literal',
                        ],
                        [
                            's' => 'http://person2',
                            's type' => 'uri',
                            'id' => '2',
                            'id type' => 'literal',
                        ],
                        [
                            's' => 'http://person1',
                            's type' => 'uri',
                            'id' => '1',
                            'id type' => 'literal',
                        ]
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }
}
