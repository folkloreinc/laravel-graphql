<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;

class GraphQLTest extends TestCase
{    

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schema.query', [
            'examples' => \Folklore\GraphQL\Tests\ExamplesQuery::class
        ]);
        
        $app['config']->set('graphql.schema.mutation', [
            'updateExample' => \Folklore\GraphQL\Tests\UpdateExampleMutation::class
        ]);
        
        $app['config']->set('graphql.types', [
            'Example' => \Folklore\GraphQL\Tests\ExampleType::class
        ]);
    }
    
    /**
     * Test schema
     *
     * @test
     */
    public function testSchema()
    {
        $schema = GraphQL::schema();
        
        $this->assertGraphQLSchema($schema);
        $this->assertGraphQLSchemaHasQuery($schema, 'examples');
        $this->assertGraphQLSchemaHasMutation($schema, 'updateExample');
    }
    
    /**
     * Test schema custom
     *
     * @test
     */
    public function testSchemaCustom()
    {
        $schema = GraphQL::schema([
            'query' => [
                'examplesCustom' => \Folklore\GraphQL\Tests\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \Folklore\GraphQL\Tests\UpdateExampleMutation::class
            ]
        ]);
        
        $this->assertGraphQLSchema($schema);
        $this->assertGraphQLSchemaHasQuery($schema, 'examplesCustom');
        $this->assertGraphQLSchemaHasMutation($schema, 'updateExampleCustom');
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
        $this->assertInstanceOf('Folklore\GraphQL\Support\Type', $type);
    }
    
    /**
     * Test get types
     *
     * @test
     */
    public function testType()
    {
        $type = GraphQL::type('Example');
        $this->assertInstanceOf('GraphQL\Type\Definition\ObjectType', $type);
    }
    
    /**
     * Test add type
     *
     * @test
     */
    public function testAddType()
    {
        GraphQL::addType(\Folklore\GraphQL\Tests\ExampleType::class, 'ExampleCustom');
        
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('ExampleCustom', $types);
        
        $type = app($types['ExampleCustom']);
        $this->assertInstanceOf('Folklore\GraphQL\Support\Type', $type);
    }
    
    /**
     * Test add query
     *
     * @test
     */
    public function testAddQuery()
    {
        GraphQL::addQuery('examplesCustom', \Folklore\GraphQL\Tests\ExamplesQuery::class);
        
        $queries = GraphQL::getQueries();
        $this->assertArrayHasKey('examplesCustom', $queries);
        
        $query = app($queries['examplesCustom']);
        $this->assertInstanceOf('Folklore\GraphQL\Support\Query', $query);
    }
    
    /**
     * Test add mutation
     *
     * @test
     */
    public function testAddMutation()
    {
        GraphQL::addMutation('updateExampleCustom', \Folklore\GraphQL\Tests\UpdateExampleMutation::class);
        
        $mutations = GraphQL::getMutations();
        $this->assertArrayHasKey('updateExampleCustom', $mutations);
        
        $mutation = app($mutations['updateExampleCustom']);
        $this->assertInstanceOf('Folklore\GraphQL\Support\Mutation', $mutation);
    }

    /**
    * Test interface implementation
    * 
    * @test
    */
    public function testInterface()
    {
        $schema = GraphQL::schema([
            'types' => [
                'ExampleInterface' => \Folklore\GraphQL\Tests\ExampleInterfaceType::class,
                'ExampleImplementer' => \Folklore\GraphQL\Tests\ExampleImplementerType::class
            ]
        ]);
        
        $interfaceType = $schema->getType('ExampleInterface');
        $implementerType = $schema->getType('ExampleImplementer');

        $isImplementation = $schema->isPossibleType($interfaceType, $implementerType);
        $this->assertTrue($isImplementation);
    }
}
