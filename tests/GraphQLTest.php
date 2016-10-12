<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class GraphQLTest extends TestCase
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
                'examplesCustom' => ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => UpdateExampleMutation::class
            ],
            'types' => [
                CustomExampleType::class
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
        $result = GraphQL::query($this->queries['examples']);
        
        $this->assertArrayHasKey('data', $result);
        
        $this->assertEquals($result['data'], [
            'examples' => $this->data
        ]);
    }
    
    public function testQueryWithParams()
    {
        $result = GraphQL::query($this->queries['examplesWithParams'], [
            'index' => 0
        ]);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($result['data'], [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }
    
    public function testQueryWithContext()
    {
        $result = GraphQL::query($this->queries['examplesWithContext'], null, [
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
        $result = GraphQL::query($this->queries['examplesCustom'], null, null, [
            'query' => [
                'examplesCustom' => ExamplesQuery::class
            ]
        ]);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($result['data'], [
            'examplesCustom' => $this->data
        ]);
    }
    
    /**
     * Test add type
     *
     * @test
     */
    public function testAddType()
    {
        GraphQL::addType(CustomExampleType::class);

        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);

        $type = app($types['CustomExample']);
        $this->assertInstanceOf(CustomExampleType::class, $type);

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
        GraphQL::addType(ExampleType::class, 'CustomExample');
        
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);
        
        $type = app($types['CustomExample']);
        $this->assertInstanceOf(ExampleType::class, $type);
        
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
