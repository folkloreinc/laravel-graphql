<?php

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }
    
    protected function assertGraphQLSchema($schema)
    {
        $this->assertInstanceOf('GraphQL\Schema', $schema);
    }
    
    protected function assertGraphQLSchemaHasQuery($schema, $key)
    {
        //Query
        $query = $schema->getQueryType();
        $queryFields = $query->getFields();
        $this->assertArrayHasKey($key, $queryFields);
        
        $queryField = $queryFields[$key];
        $queryListType = $queryField->getType();
        $queryType = $queryListType->getWrappedType();
        $this->assertInstanceOf('GraphQL\Type\Definition\FieldDefinition', $queryField);
        $this->assertInstanceOf('GraphQL\Type\Definition\ListOfType', $queryListType);
        $this->assertInstanceOf('GraphQL\Type\Definition\ObjectType', $queryType);
    }
    
    protected function assertGraphQLSchemaHasMutation($schema, $key)
    {
        //Mutation
        $mutation = $schema->getMutationType();
        $mutationFields = $mutation->getFields();
        $this->assertArrayHasKey($key, $mutationFields);
        
        $mutationField = $mutationFields[$key];
        $mutationType = $mutationField->getType();
        $this->assertInstanceOf('GraphQL\Type\Definition\FieldDefinition', $mutationField);
        $this->assertInstanceOf('GraphQL\Type\Definition\ObjectType', $mutationType);
    }
    
    protected function getPackageProviders($app)
    {
        return [
            \Folklore\GraphQL\ServiceProvider::class
        ];
    }
    
    protected function getPackageAliases($app)
    {
        return [
            'GraphQL' => \Folklore\GraphQL\Support\Facades\GraphQL::class
        ];
    }
}
