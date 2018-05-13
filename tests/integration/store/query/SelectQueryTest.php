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

    public function testSelectRelationalGreatThan()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://container1> <http://weight> "150" .
            <http://container2> <http://weight> "50" .
        }');

        $res = $this->fixture->query('SELECT ?c WHERE {
            ?c <http://weight> ?w .

            FILTER (?w > 100)
        }');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'c'
                    ],
                    'rows' => [
                        [
                            'c' => 'http://container1',
                            'c type' => 'uri',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectRelationalSmallerThan()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://container1> <http://weight> "150" .
            <http://container2> <http://weight> "50" .
        }');

        $res = $this->fixture->query('SELECT ?c WHERE {
            ?c <http://weight> ?w .

            FILTER (?w < 100)
        }');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'c'
                    ],
                    'rows' => [
                        [
                            'c' => 'http://container2',
                            'c type' => 'uri',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectRelationalEqual()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://container1> <http://weight> "150" .
            <http://container2> <http://weight> "50" .
        }');

        $res = $this->fixture->query('SELECT ?c WHERE {
            ?c <http://weight> ?w .

            FILTER (?w = 150)
        }');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'c'
                    ],
                    'rows' => [
                        [
                            'c' => 'http://container1',
                            'c type' => 'uri',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectRelationalNotEqual()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://container1> <http://weight> "150" .
            <http://container2> <http://weight> "50" .
        }');

        $res = $this->fixture->query('SELECT ?c WHERE {
            ?c <http://weight> ?w .

            FILTER (?w != 150)
        }');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'c'
                    ],
                    'rows' => [
                        [
                            'c' => 'http://container2',
                            'c type' => 'uri',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectSameTerm()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://container1> <http://weight> "100" .
            <http://container2> <http://weight> "100" .
        }');

        $res = $this->fixture->query('SELECT ?c1 ?c2 WHERE {
            ?c1 ?weight ?w1.

            ?c2 ?weight ?w2.

            FILTER (sameTerm(?w1, ?w2))
        }');
        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        'c1', 'c2'
                    ],
                    'rows' => [
                        [
                            'c1' => 'http://container1',
                            'c1 type' => 'uri',
                            'c2' => 'http://container1',
                            'c2 type' => 'uri',
                        ],
                        [
                            'c1' => 'http://container2',
                            'c1 type' => 'uri',
                            'c2' => 'http://container1',
                            'c2 type' => 'uri',
                        ],
                        [
                            'c1' => 'http://container1',
                            'c1 type' => 'uri',
                            'c2' => 'http://container2',
                            'c2 type' => 'uri',
                        ],
                        [
                            'c1' => 'http://container2',
                            'c1 type' => 'uri',
                            'c2' => 'http://container2',
                            'c2 type' => 'uri',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res,
            '',
            0,
            10,
            true
        );

        $this->markTestSkipped(
            'ARC2: solving sameterm does not work properly. The result contains elements multiple times. '
            . PHP_EOL . 'Expected behavior is described here: https://www.w3.org/TR/rdf-sparql-query/#func-sameTerm'
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
        $query = 'SELECT ?who COUNT(?person) as ?persons WHERE {
                ?who <http://knows> ?person .
            }
            GROUP BY ?who
        ';

        // mark skipped, if we have a certain MySQL version running
        $mysqlMajorVersion = substr($this->fixture->a['db_con']->server_info, 0, 3);
        if (in_array($mysqlMajorVersion, ['5.7'])) {
            $this->markTestSkipped(
                '[mysql 5.7] Result set is empty for query: '
                .$query
            );
        }

        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://knows> <http://person2>, <http://person3> .
            <http://person2> <http://knows> <http://person3> .
        }');

        $res = $this->fixture->query($query);
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
     * OFFSET and LIMIT
     */

    public function testSelectOffset()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://id> "1" .
            <http://person3> <http://id> "3" .
            <http://person2> <http://id> "2" .
        }');

        $res = $this->fixture->query('
            SELECT * WHERE { ?s ?p ?o . }
            OFFSET 1
        ');

        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        's', 'p', 'o'
                    ],
                    'rows' => [
                        [
                            's' => 'http://person3',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '3',
                            'o type' => 'literal',
                        ],
                        [
                            's' => 'http://person2',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '2',
                            'o type' => 'literal',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectOffsetLimit()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://id> "1" .
            <http://person3> <http://id> "3" .
            <http://person2> <http://id> "2" .
        }');

        $res = $this->fixture->query('
            SELECT * WHERE { ?s ?p ?o . }
            OFFSET 1 LIMIT 2
        ');

        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        's', 'p', 'o'
                    ],
                    'rows' => [
                        [
                            's' => 'http://person3',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '3',
                            'o type' => 'literal',
                        ],
                        [
                            's' => 'http://person2',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '2',
                            'o type' => 'literal',
                        ],
                    ],
                ],
                'query_time' => $res['query_time']
            ],
            $res
        );
    }

    public function testSelectLimit()
    {
        // test data
        $this->fixture->query('INSERT INTO <http://example.com/> {
            <http://person1> <http://id> "1" .
            <http://person3> <http://id> "3" .
            <http://person2> <http://id> "2" .
        }');

        $res = $this->fixture->query('
            SELECT * WHERE { ?s ?p ?o . }
            LIMIT 2
        ');

        $this->assertEquals(
            [
                'query_type' => 'select',
                'result' => [
                    'variables' => [
                        's', 'p', 'o'
                    ],
                    'rows' => [
                        [
                            's' => 'http://person1',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '1',
                            'o type' => 'literal',
                        ],
                        [
                            's' => 'http://person3',
                            's type' => 'uri',
                            'p' => 'http://id',
                            'p type' => 'uri',
                            'o' => '3',
                            'o type' => 'literal',
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

    public function testSelectOrderByWithoutContent()
    {
        $res = $this->fixture->query('
            SELECT * WHERE {
                ?s <http://id> ?id .
            }
            ORDER BY
        ');

        // query false, therefore 0 as result
        $this->assertEquals(0, $res);
    }
}
