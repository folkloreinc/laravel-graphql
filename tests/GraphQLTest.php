<?php

use Folklore\GraphQL\GraphQL as GraphQLFacade;
use GraphQL\Schema;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Events\TypeAdded;
use Folklore\GraphQL\Events\SchemaAdded;

/**
 * @coversDefaultClass \Folklore\GraphQL\GraphQL
 */
class GraphQLTest extends TestCase
{
    protected $graphql;

    public function setUp()
    {
        parent::setUp();
        $this->graphql = app('graphql');
    }

    /**
     * Test schema default
     *
     * @test
     * @covers ::schema
     */
    public function testSchema()
    {
        $schema = $this->graphql->schema();

        $this->assertGraphQLSchema($schema);

        foreach (config('graphql.schemas.default.query') as $key => $class) {
            $this->assertGraphQLSchemaHasQuery($schema, $key);
        }

        foreach (config('graphql.schemas.default.mutation') as $key => $class) {
            $this->assertGraphQLSchemaHasMutation($schema, $key);
        }

        $types = $schema->getTypeMap();
        foreach (config('graphql.types') as $key => $class) {
            $this->assertArrayHasKey($key, $types);
        }
    }

    /**
     * Test schema with object
     *
     * @test
     * @covers ::schema
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
        $schema = $this->graphql->schema($schemaObject);

        $this->assertGraphQLSchema($schema);
        $this->assertEquals($schemaObject, $schema);
    }

    /**
     * Test schema with name
     *
     * @test
     * @covers ::schema
     */
    public function testSchemaWithName()
    {
        $schema = $this->graphql->schema('custom');

        $this->assertGraphQLSchema($schema);

        foreach (config('graphql.schemas.custom.query') as $key => $class) {
            $this->assertGraphQLSchemaHasQuery($schema, $key);
        }

        foreach (config('graphql.schemas.custom.mutation') as $key => $class) {
            $this->assertGraphQLSchemaHasMutation($schema, $key);
        }

        $types = $schema->getTypeMap();
        foreach (config('graphql.types') as $key => $class) {
            $this->assertArrayHasKey($key, $types);
        }
    }

    /**
     * Test schema custom
     *
     * @test
     * @covers ::schema
     */
    public function testSchemaWithArray()
    {
        $schema = $this->graphql->schema([
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
     * @covers ::schema
     * @expectedException \Folklore\GraphQL\Exception\SchemaNotFound
     */
    public function testSchemaWithWrongName()
    {
        $schema = $this->graphql->schema('wrong');
    }

    /**
     * Test the router schema patter
     *
     * @test
     * @covers ::routerSchemaPattern
     */
    public function testRouterSchemaPattern()
    {
        $schemas = array_keys($this->graphql->getSchemas());
        $schemaPattern = $this->graphql->routerSchemaPattern();

        $this->assertEquals($schemaPattern, '('.implode('|', $schemas).')');
        $this->assertRegExp('/'.$schemaPattern.'/', $schemas[0]);
    }

    /**
     * Test type
     *
     * @test
     * @covers ::type
     */
    public function testType()
    {
        $type = $this->graphql->type('Example');
        $this->assertInstanceOf(ObjectType::class, $type);

        $typeOther = $this->graphql->type('Example');
        $this->assertTrue($type === $typeOther);

        $typeOther = $this->graphql->type('Example', true);
        $this->assertFalse($type === $typeOther);
    }

    /**
     * Test wrong type
     *
     * @test
     * @covers ::type
     * @expectedException \Folklore\GraphQL\Exception\TypeNotFound
     */
    public function testWrongType()
    {
        $typeWrong = $this->graphql->type('ExampleWrong');
    }

    /**
     * Test objectType from an objectType
     *
     * @test
     * @covers ::objectType
     */
    public function testObjectType()
    {
        $objectType = new ObjectType([
            'name' => 'ObjectType'
        ]);
        $type = $this->graphql->objectType($objectType, [
            'name' => 'ExampleType'
        ]);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertEquals($objectType, $type);
        $this->assertEquals($type->name, 'ExampleType');
    }

    /**
     * Test objectType from fields
     *
     * @test
     * @covers ::objectType
     * @covers ::buildObjectTypeFromFields
     */
    public function testObjectTypeFromFields()
    {
        $type = $this->graphql->objectType([
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ],
            'name_string' => \App\GraphQL\Field\ExampleField::class
        ], [
            'name' => 'ExampleType'
        ]);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertEquals($type->name, 'ExampleType');
        $fields = $type->getFields();
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('name_string', $fields);
    }

    /**
     * Test objectType from class
     *
     * @test
     * @covers ::objectType
     * @covers ::buildObjectTypeFromClass
     */
    public function testObjectTypeClass()
    {
        $type = $this->graphql->objectType(\App\GraphQL\Type\ExampleType::class, [
            'name' => 'ExampleType'
        ]);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertEquals($type->name, 'ExampleType');
        $fields = $type->getFields();
        $this->assertInternalType('array', $fields);
        $this->assertArrayHasKey('name', $fields);
    }

    /**
     * Test format error
     *
     * @test
     * @covers ::formatError
     */
    public function testFormatError()
    {
        $result = $this->graphql->queryAndReturnResult($this->queries['examplesWithError']);
        $error = $this->graphql->formatError($result->errors[0]);

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

    /**
     * Test format error with validation error
     *
     * @test
     * @covers ::formatError
     */
    public function testFormatValidationError()
    {
        $validator = Validator::make([], [
            'name' => 'required'
        ]);
        $validator->fails();
        $validationError = with(new ValidationError('validation'))->setValidator($validator);
        $error = new Error('error', null, null, null, null, $validationError);
        $error = $this->graphql->formatError($error);

        $this->assertInternalType('array', $error);
        $this->assertArrayHasKey('validation', $error);
        $this->assertTrue($error['validation']->has('name'));
    }

    /**
     * Test add type
     *
     * @test
     * @covers ::addType
     * @covers ::getTypeName
     */
    public function testAddType()
    {
        $this->expectsEvents(TypeAdded::class);

        $this->graphql->addType(\App\GraphQL\Type\CustomExampleType::class);

        $types = $this->graphql->getTypes();
        $this->assertArrayHasKey('CustomExample', $types);

        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\App\GraphQL\Type\CustomExampleType::class, $type);

        $type = $this->graphql->type('CustomExample');
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
    }

    /**
     * Test add type with a name
     *
     * @test
     * @covers ::addType
     */
    public function testAddTypeWithName()
    {
        $this->graphql->addType(\App\GraphQL\Type\ExampleType::class, 'CustomExample');

        $types = $this->graphql->getTypes();
        $this->assertArrayHasKey('CustomExample', $types);

        $type = app($types['CustomExample']);
        $this->assertInstanceOf(\App\GraphQL\Type\ExampleType::class, $type);

        $type = $this->graphql->type('CustomExample');
        $this->assertInstanceOf(\GraphQL\Type\Definition\ObjectType::class, $type);
    }

    /**
     * Test add types
     *
     * @test
     * @covers ::addTypes
     */
    public function testAddTypes()
    {
        $type = new \App\GraphQL\Type\ExampleType();
        $type->name = 'CustomExampleInstance';

        $this->graphql->addTypes([
            'CustomExampleName' => \App\GraphQL\Type\ExampleType::class,
            \App\GraphQL\Type\CustomExampleType::class,
            $type
        ]);

        $types = $this->graphql->getTypes();
        $this->assertArrayHasKey('CustomExample', $types);
        $this->assertArrayHasKey('CustomExampleName', $types);
        $this->assertArrayHasKey('CustomExampleInstance', $types);
    }

    /**
     * Test get types
     *
     * @test
     * @covers ::getTypes
     */
    public function testGetTypes()
    {
        $types = $this->graphql->getTypes();
        $this->assertArrayHasKey('Example', $types);

        $type = app($types['Example']);
        $this->assertInstanceOf(\Folklore\GraphQL\Support\Type::class, $type);
    }

    /**
     * Test clearing types
     *
     * @test
     * @covers ::clearType
     * @covers ::clearTypes
     */
    public function testClearTypes()
    {
        $this->graphql->clearType('Example');
        $types = $this->graphql->getTypes();
        $this->assertArrayNotHasKey('Example', $types);
        $this->assertArrayHasKey('ExampleInterface', $types);
        $this->graphql->clearTypes();
        $types = $this->graphql->getTypes();
        $this->assertEquals([], $types);
    }

    /**
     * Test add schema
     *
     * @test
     * @covers ::addSchema
     */
    public function testAddSchema()
    {
        $this->expectsEvents(SchemaAdded::class);

        $this->graphql->addSchema('custom_add', [
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

        $schemas = $this->graphql->getSchemas();
        $this->assertArrayHasKey('custom_add', $schemas);
    }

    /**
     * Test add schemas
     *
     * @test
     * @covers ::addSchemas
     */
    public function testAddSchemas()
    {
        $this->expectsEvents(SchemaAdded::class);

        $this->graphql->addSchemas([
            'custom_add' => [
                'query' => [
                    'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
                ],
                'mutation' => [
                    'updateExampleCustom' => \App\GraphQL\Mutation\UpdateExampleMutation::class
                ],
                'types' => [
                    \App\GraphQL\Type\CustomExampleType::class
                ]
            ]
        ]);

        $schemas = $this->graphql->getSchemas();
        $this->assertArrayHasKey('custom_add', $schemas);
    }

    /**
     * Test get schemas
     *
     * @test
     * @covers ::getSchemas
     */
    public function testGetSchemas()
    {
        $schemas = $this->graphql->getSchemas();
        $this->assertArrayHasKey('default', $schemas);
        $this->assertArrayHasKey('custom', $schemas);
        $this->assertInternalType('array', $schemas['default']);
        $this->assertInternalType('array', $schemas['custom']);
    }

    /**
     * Test clearing schemas
     *
     * @test
     * @covers ::clearSchema
     * @covers ::clearSchemas
     */
    public function testClearSchemas()
    {
        $this->graphql->clearSchema('default');
        $schemas = $this->graphql->getSchemas();
        $this->assertArrayNotHasKey('default', $schemas);
        $this->assertArrayHasKey('custom', $schemas);
        $this->graphql->clearSchemas();
        $schemas = $this->graphql->getSchemas();
        $this->assertEquals([], $schemas);
    }

    /**
     * Test default schema
     *
     * @test
     * @covers ::setDefaultSchema
     * @covers ::getDefaultSchema
     */
    public function testGetDefaultSchema()
    {
        $schema = 'custom';
        $this->graphql->setDefaultSchema($schema);
        $this->assertEquals($schema, $this->graphql->getDefaultSchema());
    }

    /**
     * Test max query depth
     *
     * @test
     * @covers ::getMaxQueryDepth
     * @covers ::setMaxQueryDepth
     */
    public function testMaxQueryDepth()
    {
        $initialValue = $this->graphql->getMaxQueryDepth();
        $value = 121;
        $this->graphql->setMaxQueryDepth($value);
        $this->assertEquals($value, $this->graphql->getMaxQueryDepth());
        $this->graphql->setMaxQueryDepth($initialValue);
    }

    /**
     * Test max query complexity
     *
     * @test
     * @covers ::getMaxQueryComplexity
     * @covers ::setMaxQueryComplexity
     */
    public function testMaxQueryComplexity()
    {
        $initialValue = $this->graphql->getMaxQueryComplexity();
        $value = 121;
        $this->graphql->setMaxQueryComplexity($value);
        $this->assertEquals($value, $this->graphql->getMaxQueryComplexity());
        $this->graphql->setMaxQueryComplexity($initialValue);
    }

    /**
     * Test introspection query
     *
     * @test
     * @covers ::introspectionQuery
     * @covers ::loadIntrospectionQuery
     * @covers ::setIntrospectionQuery
     */
    public function testIntrospectionQuery()
    {
        $query = $this->graphql->introspectionQuery();
        $queryFromFile = file_get_contents(__DIR__.'/../src/resources/graphql/introspectionQuery.txt');
        $this->assertEquals($queryFromFile, $query);

        $baseQuery = 'query';
        $this->graphql->setIntrospectionQuery($baseQuery);
        $query = $this->graphql->introspectionQuery();
        $this->assertEquals($baseQuery, $query);
    }

    /**
     * Test introspection
     *
     * @test
     * @covers ::introspection
     */
    public function testIntrospection()
    {
        $return = $this->graphql->introspection();
        $this->assertArrayHasKey('data', $return);
        $data = $return['data'];
        $this->assertArrayHasKey('__schema', $data);

        //Assert that all type exists
        $schema = $this->graphql->schema();
        $schemaTypes = array_get($data, '__schema.types');
        $schemaTypesNames = array_pluck($schemaTypes, 'name');
        $typesNames = array_keys($this->graphql->getTypes());
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
