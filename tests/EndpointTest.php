<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class EndpointTest extends TestCase
{

    protected $queries;
    protected $data;
    
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->queries = include(__DIR__.'/Objects/queries.php');
        $this->data = include(__DIR__.'/Objects/data.php');
    }
    
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas.default', [
            'query' => [
                'examples' => ExamplesQuery::class,
                'examplesContext' => ExamplesContextQuery::class
            ],
            'mutation' => [
                'updateExample' => UpdateExampleMutation::class
            ]
        ]);
        
        $app['config']->set('graphql.schemas.custom', [
            'query' => [
                'examplesCustom' => ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => UpdateExampleMutation::class
            ]
        ]);
        
        $app['config']->set('graphql.types', [
            'Example' => ExampleType::class
        ]);
    }
    
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
            'params' => [
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
     * Test get with params
     *
     * @test
     */
    public function testGetWithValidation()
    {
        $response = $this->call('GET', '/graphql', [
            'query' => $this->queries['examplesWithValidation']
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('validation', $content['errors'][0]);
        $this->assertTrue($content['errors'][0]['validation']->has('index'));
    }
}
