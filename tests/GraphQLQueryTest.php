<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;

class GraphQLQueryTest extends TestCase
{
    /**
     * Test query
     *
     * @test
     */
    public function testQueryAndReturnResult()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertObjectHasAttribute('data', $result);
        
        $this->assertEquals($result->data, [
            'examples' => array_map(function ($item) {
                return array_only($item, ['id', 'name']);
            }, \App\Data::get())
        ]);
    }
    
    /**
     * Test query methods
     *
     * @test
     */
    public function testQuery()
    {
        $resultArray = GraphQL::query($this->queries['examples']);
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);
        
        $this->assertInternalType('array', $resultArray);
        $this->assertArrayHasKey('data', $resultArray);
        $this->assertEquals($resultArray['data'], $result->data);
    }
    
    /**
     * Test query with params
     *
     * @test
     */
    public function testQueryAndReturnResultWithParams()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithParams'], [
            'id' => 1
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examples' => [
                \App\Data::getById(1)
            ]
        ]);
    }
    
    /**
     * Test query with initial root
     *
     * @test
     */
    public function testQueryAndReturnResultWithRoot()
    {
        $context = [
            'id' => 1,
            'name' => 'root'
        ];
        
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithRoot'], null, [
            'root' => $context
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesRoot' => $context
        ]);
    }
    
    /**
     * Test query with context
     *
     * @test
     */
    public function testQueryAndReturnResultWithContext()
    {
        $context = [
            'id' => 1,
            'name' => 'context'
        ];
        
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithContext'], null, [
            'context' => $context
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesContext' => $context
        ]);
    }
    
    /**
     * Test query with schema
     *
     * @test
     */
    public function testQueryAndReturnResultWithSchema()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'], null, [
            'schema' => [
                'query' => [
                    'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
                ]
            ]
        ]);
        
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesCustom' => array_map(function ($item) {
                return array_only($item, ['id', 'name']);
            }, \App\Data::get())
        ]);
    }
    
    /**
     * Test query with error
     *
     * @test
     */
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
     * Test query with validation error
     *
     * @test
     */
    public function testQueryWithValidationError()
    {
        $result = GraphQL::query($this->queries['examplesWithValidation']);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('validation', $result['errors'][0]);
        $this->assertTrue($result['errors'][0]['validation']->has('id'));
    }
    
    /**
     * Test query with validation without error
     *
     * @test
     */
    public function testQueryWithValidation()
    {
        $result = GraphQL::query($this->queries['examplesWithValidation'], [
            'id' => 0
        ]);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }
}
