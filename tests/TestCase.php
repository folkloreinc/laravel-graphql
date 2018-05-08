<?php

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
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
                'examplesRoot' => ExamplesRootQuery::class,
                'examplesAuthorize' => ExamplesAuthorizeQuery::class,
                'examplesCustomAuthorize' => ExamplesCustomAuthorizeQuery::class,
                'examplesAuthenticated' => ExamplesAuthenticatedQuery::class,
                'examplesPagination' => ExamplesPaginationQuery::class,
            ],
            'mutation' => [
                'updateExample' => UpdateExampleMutation::class
            ]
        ]);

        $app['config']->set('graphql.schemas.custom', [
            'query' => [
                'examplesCustom' => ExamplesQuery::class,
            ],
            'mutation' => [
                'updateExampleCustom' => UpdateExampleMutation::class
            ]
        ]);

        $app['config']->set('graphql.types', [
            'Example' => ExampleType::class
        ]);
    }

    protected function assertGraphQLSchema($schema)
    {
        $this->assertInstanceOf('GraphQL\Type\Schema', $schema);
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
