<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error\Error;
use Folklore\GraphQL\Error\ValidationError;
use App\Data;

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

        $data = Data::get();
        unset($data[1]['items']);

        $this->assertEquals($result->data, [
            'examples' => $data
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
     * Test query with variables
     *
     * @test
     */
    public function testQueryAndReturnResultWithVariables()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithVariables'], [
            'id' => 1
        ]);

        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examples' => [
                Data::getById(1)
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
        $data = [
            'id' => 'test',
            'name' => 'test_with_root'
        ];
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithRoot'], null, [
            'root' => $data
        ]);

        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesRoot' => $data
        ]);
    }

    /**
     * Test query with context
     *
     * @test
     */
    public function testQueryAndReturnResultWithContext()
    {
        $data = [
            'id' => 'test',
            'name' => 'test_with_context'
        ];
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithContext'], null, [
            'context' => $data
        ]);
        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesContext' => $data
        ]);
    }

    /**
     * Test query with authorize
     *
     * @test
     */
    public function testQueryAndReturnResultWithAuthorize()
    {
        $result = GraphQL::query($this->queries['examplesWithAuthorize']);
        $this->assertNull($result['data']['examplesAuthorize']);
        $this->assertEquals('Unauthorized', $result['errors'][0]['message']);
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

        $data = Data::get();
        unset($data[1]['items']);

        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesCustom' => $data
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
            'id' => 1
        ]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }
}
