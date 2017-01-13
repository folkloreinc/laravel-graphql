<?php

use Orchestra\Testbench\TestCase as BaseTestCase;
use GraphQL\Type\Definition\ListOfType;

abstract class TestCase extends BaseTestCase
{
    protected $queries;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->queries = include(__DIR__.'/fixture/queries.php');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas.default', [
            'query' => [
                'examples' => \App\GraphQL\Query\ExamplesQuery::class,
                'examplesContext' => \App\GraphQL\Query\ExamplesContextQuery::class,
                'examplesRoot' => \App\GraphQL\Query\ExamplesRootQuery::class
            ],
            'mutation' => [
                'updateExample' => \App\GraphQL\Mutation\UpdateExampleMutation::class
            ]
        ]);

        $app['config']->set('graphql.schemas.custom', [
            'query' => [
                'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
            ],
            'mutation' => [
                'updateExampleCustom' => \App\GraphQL\Mutation\UpdateExampleMutation::class
            ]
        ]);

        $app['config']->set('graphql.types', [
            'ExampleInterface' => \App\GraphQL\Type\ExampleInterfaceType::class,
            'Example' => \App\GraphQL\Type\ExampleType::class
        ]);
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
        $queryType = $queryField->getType();
        $isList = $queryType instanceof ListOfType;
        $queryType = $isList ? $queryType->getWrappedType():$queryType;
        $this->assertInstanceOf('GraphQL\Type\Definition\FieldDefinition', $queryField);
        if (!$isList) {
            $this->assertInstanceOf('GraphQL\Type\Definition\ObjectType', $queryType);
        }
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
