<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;

class GraphQLQueryTest extends TestCase
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
                'examplesContext' => ExamplesContextQuery::class,
                'examplesRoot' => ExamplesRootQuery::class
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
     * Test queryAndReturnResult
     *
     * @test
     */
    public function testQueryAndReturnResult()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertObjectHasAttribute('data', $result);
        
        $this->assertEquals($result->data, [
            'examples' => $this->data
        ]);
    }
    
    public function testQueryAndReturnResultWithParams()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithParams'], [
            'index' => 0
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertEquals($result->data, [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }
    
    public function testQueryAndReturnResultWithRoot()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithRoot'], null, [
            'root' => [
                'test' => 'root'
            ]
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertEquals($result->data, [
            'examplesRoot' => [
                'test' => 'root'
            ]
        ]);
    }
    
    public function testQueryAndReturnResultWithContext()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithContext'], null, [
            'context' => [
                'test' => 'context'
            ]
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertEquals($result->data, [
            'examplesContext' => [
                'test' => 'context'
            ]
        ]);
    }
    
    public function testQueryAndReturnResultWithSchema()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'], null, [
            'schema' => [
                'query' => [
                    'examplesCustom' => ExamplesQuery::class
                ]
            ]
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertEquals($result->data, [
            'examplesCustom' => $this->data
        ]);
    }
    
    public function testQuery()
    {
        $resultArray = GraphQL::query($this->queries['examples']);
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertInternalType('array', $resultArray);
        $this->assertArrayHasKey('data', $resultArray);
        $this->assertEquals($resultArray['data'], $result->data);
    }
    
    public function testQueryWithError()
    {
        $result = GraphQL::query($this->queries['examplesWithError']);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNull($result['data']);
        $this->assertCount(1, $result['errors']);
        $this->assertArrayHasKey('message', $result['errors'][0]);
        $this->assertArrayHasKey('locations', $result['errors'][0]);
    }
    
    /**
     * Test get with validation error
     *
     * @test
     */
    public function testQueryWithValidationError()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithValidation']);
        
        dd($result);
        
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('validation', $content['errors'][0]);
        $this->assertTrue($content['errors'][0]['validation']->has('index'));
    }
    
    public function testGetWithValidation()
    {
        $response = $this->call('GET', '/graphql', [
            'query' => $this->queries['examplesWithValidation'],
            'params' => [
                'index' => 1
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayNotHasKey('errors', $content);
    }
}
