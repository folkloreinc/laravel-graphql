<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class GraphQLTest extends TestCase
{    
    protected $exampleQuery = "
        query QueryExamples {
            examples {
                test
            }
        }
    ";
      
    protected $exampleQueryCustom = "
        query QueryExamplesCustom {
            examplesCustom {
                test
            }
        }
    ";
    
    protected $exampleQueryWithParams = "
        query QueryExamplesParams(\$index: Int) {
            examples(index: \$index) {
                test
            }
        }
    ";
    
    protected $exampleQueryWithContext = "
        query QueryExamplesContext {
            examplesContext {
                test
            }
        }
    ";

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas.default', [
            'query' => [
                'examples' => \Folklore\GraphQL\Tests\Objects\ExamplesQuery::class,
                'examplesContext' => \Folklore\GraphQL\Tests\Objects\ExamplesContextQuery::class
            ],
            'mutation' => [
                'updateExample' => \Folklore\GraphQL\Tests\Objects\UpdateExampleMutation::class
            ]
        ]);
        
        $app['config']->set('graphql.schemas.custom', [
            'query' => [
                'examplesCustom' => \Folklore\GraphQL\Tests\Objects\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \Folklore\GraphQL\Tests\Objects\UpdateExampleMutation::class
            ]
        ]);
        
        $app['config']->set('graphql.types', [
            'Example' => \Folklore\GraphQL\Tests\Objects\ExampleType::class
        ]);
    }
    
    /**
     * Test schema default
     *
     * @test
     */
    public function testSchema()
    {
        $schema = GraphQL::schema();
        
        $this->assertGraphQLSchema($schema);
        $this->assertGraphQLSchemaHasQuery($schema, 'examples');
        $this->assertGraphQLSchemaHasMutation($schema, 'updateExample');
        $this->assertArrayHasKey('Example', $schema->getTypeMap());
    }
    
    /**
     * Test schema with object
     *
     * @test
     */
    public function testSchemaWithSchemaObject()
    {
        $schemaObject = new Schema([
            'query' => new ObjectType([
                'name' => 'Query'
            ]),
            'mutation' => new ObjectType([
                'name' => 'Mutation'
            ]),
            'types' => []
        ]);
        $schema = GraphQL::schema($schemaObject);
        
        $this->assertGraphQLSchema($schema);
        $this->assertEquals($schemaObject, $schema);
    }
    
    /**
     * Test schema with name
     *
     * @test
     */
    public function testSchemaWithName()
    {
        $schema = GraphQL::schema('custom');
        
        $this->assertGraphQLSchema($schema);
        $this->assertGraphQLSchemaHasQuery($schema, 'examplesCustom');
        $this->assertGraphQLSchemaHasMutation($schema, 'updateExampleCustom');
        $this->assertArrayHasKey('Example', $schema->getTypeMap());
    }
    
    /**
     * Test schema custom
     *
     * @test
     */
    public function testSchemaWithArray()
    {
        $schema = GraphQL::schema([
            'query' => [
                'examplesCustom' => \Folklore\GraphQL\Tests\Objects\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \Folklore\GraphQL\Tests\Objects\UpdateExampleMutation::class
            ],
            'types' => [
                \Folklore\GraphQL\Tests\Objects\CustomExampleType::class
            ]
        ]);
        
        $this->assertGraphQLSchema($schema);
        $this->assertGraphQLSchemaHasQuery($schema, 'examplesCustom');
        $this->assertGraphQLSchemaHasMutation($schema, 'updateExampleCustom');
        $this->assertArrayHasKey('CustomExample', $schema->getTypeMap());
    }
    
    /**
     * Test schema with wrong name
     *
     * @test
     */
    public function testSchemaWithWrongName()
    {
        $this->expectException(\Folklore\GraphQL\Exception\SchemaNotFound::class);
        $schema = GraphQL::schema('wrong');
    }
    
    /**
     * Test type
     *
     * @test
     */
    public function testType()
    {
        $type = GraphQL::type('Example');
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
        
        $typeOther = GraphQL::type('Example');
        $this->assertTrue($type === $typeOther);
        
        $typeOther = GraphQL::type('Example', true);
        $this->assertFalse($type === $typeOther);
        
        $this->expectException(\Folklore\GraphQL\Exception\TypeNotFound::class);
        $typeWrong = GraphQL::type('ExampleWrong');
    }
    
    /**
     * Test query
     *
     * @test
     */
    public function testQuery()
    {
        $result = GraphQL::query($this->exampleQuery);
        
        $this->assertArrayHasKey('data', $result);
        $data = include(__DIR__.'/Objects/data.php');
        $this->assertEquals($result['data'], [
            'examples' => $data
        ]);
    }
    
    public function testQueryWithParams()
    {
        $result = GraphQL::query($this->exampleQueryWithParams, [
            'index' => 0
        ]);
        
        $this->assertArrayHasKey('data', $result);
        $data = include(__DIR__.'/Objects/data.php');
        $this->assertEquals($result['data'], [
            'examples' => [
                $data[0]
            ]
        ]);
    }
    
    public function testQueryWithContext()
    {
        $result = GraphQL::query($this->exampleQueryWithContext, null, [
            'test' => 'context'
        ]);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($result['data'], [
            'examplesContext' => [
                'test' => 'context'
            ]
        ]);
    }
    
    public function testQueryWithSchema()
    {
        $result = GraphQL::query($this->exampleQueryCustom, null, null, [
            'query' => [
                'examplesCustom' => \Folklore\GraphQL\Tests\Objects\ExamplesQuery::class
            ]
        ]);
        $this->assertArrayHasKey('data', $result);
        $data = include(__DIR__.'/Objects/data.php');
        $this->assertEquals($result['data'], [
            'examplesCustom' => $data
        ]);
    }
    
    /**
     * Test add type
     *
     * @test
     */
    public function testAddType()
    {
        GraphQL::addType(\Folklore\GraphQL\Tests\Objects\CustomExampleType::class);

        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);

        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\Folklore\GraphQL\Tests\Objects\CustomExampleType::class, $type);

        $type = GraphQL::type('CustomExample');
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
    }
    
    /**
     * Test add type with a name
     *
     * @test
     */
    public function testAddTypeWithName()
    {
        GraphQL::addType(\Folklore\GraphQL\Tests\Objects\ExampleType::class, 'CustomExample');
        
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);
        
        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\Folklore\GraphQL\Tests\Objects\ExampleType::class, $type);
        
        $type = GraphQL::type('CustomExample');
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
    }
    
    /**
     * Test get types
     *
     * @test
     */
    public function testGetTypes()
    {
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('Example', $types);
        
        $type = app($types['Example']);
        $this->assertInstanceOf(\Folklore\GraphQL\Support\Type::class, $type);
    }
}
