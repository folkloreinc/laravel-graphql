<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use App\Data;

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

        $content = $response->getData(true);
        $this->assertArrayHasKey('data', $content);
        $data = Data::get();
        unset($data[1]['items']);
        $this->assertEquals($content['data'], [
            'examples' => $data
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

        $content = $response->getData(true);
        $this->assertArrayHasKey('data', $content);
        $data = Data::get();
        unset($data[1]['items']);
        $this->assertEquals($content['data'], [
            'examplesCustom' => $data
        ]);
    }

    /**
     * Test get with variables
     *
     * @test
     */
    public function testGetWithVariables()
    {
        $response = $this->call('GET', '/graphql', [
            'query' => $this->queries['examplesWithVariables'],
            'variables' => [
                'id' => 1
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getData(true);
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examples' => [
                Data::getById(1)
            ]
        ]);
    }

    /**
     * Test support batched queries
     *
     * @test
     */
    public function testBatchedQueries()
    {
        $response = $this->call('GET', '/graphql', [
            [
                'query' => $this->queries['examplesWithVariables'],
                'variables' => [
                    'id' => 1
                ]
            ],
            [
                'query' => $this->queries['examplesWithVariables'],
                'variables' => [
                    'id' => 1
                ]
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getData(true);
        $this->assertArrayHasKey(0, $content);
        $this->assertArrayHasKey(1, $content);
        $this->assertEquals($content[0]['data'], [
            'examples' => [
                Data::getById(1)
            ]
        ]);
        $this->assertEquals($content[1]['data'], [
            'examples' => [
                Data::getById(1)
            ]
        ]);
    }
}
