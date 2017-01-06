<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Events\TypeAdded;
use Folklore\GraphQL\Events\SchemaAdded;

class GraphQLTest extends TestCase
{
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
    public function testSchemaFromSchemaObject()
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
                'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \App\GraphQL\Mutation\UpdateExampleMutation::class
            ],
            'types' => [
                \App\GraphQL\Type\CustomExampleType::class
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
     * @expectedException \Folklore\GraphQL\Exception\SchemaNotFound
     */
    public function testSchemaWithWrongName()
    {
        $schema = GraphQL::schema('wrong');
    }
    
    /**
     * Test the router schema patter
     *
     * @test
     */
    public function testRouterSchemaPattern()
    {
        $schemas = array_keys(GraphQL::getSchemas());
        $schemaPattern = GraphQL::routerSchemaPattern();
        
        $this->assertEquals($schemaPattern, '('.implode('|', $schemas).')');
        $this->assertRegExp('/'.$schemaPattern.'/', $schemas[0]);
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
    }
    
    /**
     * Test wrong type
     *
     * @test
     * @expectedException \Folklore\GraphQL\Exception\TypeNotFound
     */
    public function testWrongType()
    {
        $typeWrong = GraphQL::type('ExampleWrong');
    }
    
    /**
     * Test objectType
     *
     * @test
     */
    public function testObjectType()
    {
        $objectType = new ObjectType([
            'name' => 'ObjectType'
        ]);
        $type = GraphQL::objectType($objectType, [
            'name' => 'ExampleType'
        ]);
        
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
        $this->assertEquals($objectType, $type);
        $this->assertEquals($type->name, 'ExampleType');
    }
    
    public function testObjectTypeFromFields()
    {
        $type = GraphQL::objectType([
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ]
        ], [
            'name' => 'ExampleType'
        ]);

        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
        $this->assertEquals($type->name, 'ExampleType');
        $fields = $type->getFields();
        $this->assertArrayHasKey('name', $fields);
    }
    
    public function testObjectTypeClass()
    {
        $type = GraphQL::objectType(\App\GraphQL\Type\ExampleType::class, [
            'name' => 'ExampleType'
        ]);

        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
        $this->assertEquals($type->name, 'ExampleType');
        $fields = $type->getFields();
        $this->assertInternalType('array', $fields);
        $this->assertArrayHasKey('name', $fields);
    }
    
    public function testFormatError()
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithError']);
        $error = GraphQL::formatError($result->errors[0]);
        
        $this->assertInternalType('array', $error);
        $this->assertArrayHasKey('message', $error);
        $this->assertArrayHasKey('locations', $error);
        $this->assertEquals($error, [
            'message' => 'Cannot query field "examplesQueryNotFound" on type "Query".',
            'locations' => [
                [
                    'line' => 3,
                    'column' => 13
                ]
            ]
        ]);
    }
    
    public function testFormatValidationError()
    {
        $validator = Validator::make([], [
            'name' => 'required'
        ]);
        $validator->fails();
        $validationError = with(new ValidationError('validation'))->setValidator($validator);
        $error = new Error('error', null, $validationError);
        $error = GraphQL::formatError($error);
        
        $this->assertInternalType('array', $error);
        $this->assertArrayHasKey('validation', $error);
        $this->assertTrue($error['validation']->has('name'));
    }
    
    /**
     * Test add type
     *
     * @test
     */
    public function testAddType()
    {
        $this->expectsEvents(TypeAdded::class);
        
        GraphQL::addType(\App\GraphQL\Type\CustomExampleType::class);

        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);

        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\App\GraphQL\Type\CustomExampleType::class, $type);

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
        GraphQL::addType(\App\GraphQL\Type\ExampleType::class, 'CustomExample');
        
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('CustomExample', $types);
        
        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\App\GraphQL\Type\ExampleType::class, $type);
        
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
    
    /**
     * Test add schema
     *
     * @test
     */
    public function testAddSchema()
    {
        $this->expectsEvents(SchemaAdded::class);
        
        GraphQL::addSchema('custom_add', [
            'query' => [
                'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \App\GraphQL\Mutation\UpdateExampleMutation::class
            ],
            'types' => [
                \App\GraphQL\Type\CustomExampleType::class
            ]
        ]);

        $schemas = GraphQL::getSchemas();
        $this->assertArrayHasKey('custom_add', $schemas);
    }
    
    /**
     * Test get schemas
     *
     * @test
     */
    public function testGetSchemas()
    {
        $schemas = GraphQL::getSchemas();
        $this->assertArrayHasKey('default', $schemas);
        $this->assertArrayHasKey('custom', $schemas);
        $this->assertInternalType('array', $schemas['default']);
    }
    
    /**
     * Test max query depth
     *
     * @test
     */
    public function testMaxQueryDepth()
    {
        $initialValue = GraphQL::getMaxQueryDepth();
        $value = 121;
        GraphQL::setMaxQueryDepth($value);
        $this->assertEquals($value, GraphQL::getMaxQueryDepth());
        GraphQL::setMaxQueryDepth($initialValue);
    }
    
    /**
     * Test max query complexity
     *
     * @test
     */
    public function testMaxQueryComplexity()
    {
        $initialValue = GraphQL::getMaxQueryComplexity();
        $value = 121;
        GraphQL::setMaxQueryComplexity($value);
        $this->assertEquals($value, GraphQL::getMaxQueryComplexity());
        GraphQL::setMaxQueryComplexity($initialValue);
    }
    
    /**
     * Test introspection query
     *
     * @test
     */
    public function testIntrospectionQuery()
    {
        $query = GraphQL::introspectionQuery();
        $queryFromFile = file_get_contents(__DIR__.'/../src/resources/graphql/introspectionQuery.txt');
        $this->assertEquals($queryFromFile, $query);
    }
    
    /**
     * Test introspection
     *
     * @test
     */
    public function testIntrospection()
    {
        $return = GraphQL::introspection();
        $this->assertArrayHasKey('data', $return);
        $data = $return['data'];
        $this->assertArrayHasKey('__schema', $data);
        
        //Assert that all type exists
        $schema = GraphQL::schema();
        $schemaTypes = array_get($data, '__schema.types');
        $schemaTypesNames = array_pluck($schemaTypes, 'name');
        $typesNames = array_keys(GraphQL::getTypes());
        foreach ($typesNames as $typeName) {
            $this->assertContains($typeName, $schemaTypesNames);
        }
        
        //Assert that all query and mutation exist
        $types = ['Query', 'Mutation'];
        foreach ($types as $type) {
            $this->assertContains($type, $schemaTypesNames);
            $schemaType = null;
            foreach ($schemaTypes as $item) {
                if ($item['name'] === $type) {
                    $schemaType = $item;
                    break;
                }
            }
            $typeFieldsNames = array_keys($schema->getType($type)->getFields());
            $schemaTypeFieldsNames = array_pluck($schemaType['fields'], 'name');
            foreach ($typeFieldsNames as $fieldname) {
                $this->assertContains($fieldname, $schemaTypeFieldsNames);
            }
        }
    }
}
