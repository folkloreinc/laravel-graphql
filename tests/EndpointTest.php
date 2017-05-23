<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class EndpointTest extends TestCase
{
    /**
     * Test get with default schema
     *
     * @test
     */
    public function testGetDefault()
    {
        $response = $this->call('GET', '/graphql', [
            'query' => $this->queries['examples']
        ]);
        
        $this->assertEquals($response->getStatusCode(), 200);
        
        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examples' => $this->data
        ]);
    }
    
    /**
     * Test get with custom schema
     *
     * @test
     */
    public function testGetCustom()
    {
        $response = $this->call('GET', '/graphql/custom', [
            'query' => $this->queries['examplesCustom']
        ]);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examplesCustom' => $this->data
        ]);
    }
    
    /**
     * Test get with params
     *
     * @test
     */
    public function testGetWithParams()
    {
        $response = $this->call('GET', '/graphql', [
            'query' => $this->queries['examplesWithParams'],
            'variables' => [
                'index' => 0
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }

    /**
     * Test support batched queries
     *
     * @test
     */
    public function testBatchedQueries() {
        $response = $this->call('GET', '/graphql', [
            [
                'query' => $this->queries['examplesWithParams'],
                'variables' => [
                    'index' => 0
                ]
            ],
            [
                'query' => $this->queries['examplesWithParams'],
                'variables' => [
                    'index' => 0
                ]
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey(0, $content);
        $this->assertArrayHasKey(1, $content);
        $this->assertEquals($content[0]['data'], [
            'examples' => [
                $this->data[0]
            ]
        ]);
        $this->assertEquals($content[1]['data'], [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }
}
