<?php

use Folklore\GraphQL\GraphQL as GraphQLFacade;
use GraphQL\Schema;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Events\TypeAdded;
use Folklore\GraphQL\Events\SchemaAdded;
use GraphQL\Validator\DocumentValidator;

/**
 * Test the Service Provider
 *
 * @coversDefaultClass \Folklore\GraphQL\ServiceProvider
 */
class ServiceProviderTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql', [

            'routes_prefix' => 'graphql_test',

            'routes' => [
                'query' => 'query/{graphql_schema?}',
                'mutation' => 'mutation/{graphql_schema?}'
            ],

            'request_variables_name' => 'params',

            'schema' => 'custom',

            'schemas' => [
                'default' => [
                    'query' => [
                        'examples' => \App\GraphQL\Query\ExamplesQuery::class,
                        'examplesContext' => \App\GraphQL\Query\ExamplesContextQuery::class,
                        'examplesRoot' => \App\GraphQL\Query\ExamplesRootQuery::class
                    ],
                    'mutation' => [
                        'updateExample' => \App\GraphQL\Mutation\UpdateExampleMutation::class
                    ]
                ],
                'custom' => [
                    'query' => [
                        'examplesCustom' => \App\GraphQL\Query\ExamplesQuery::class
                    ],
                    'mutation' => [
                        'updateExampleCustom' => \App\GraphQL\Mutation\UpdateExampleMutation::class
                    ]
                ]
            ],

            'types' => [
                'Example' => \App\GraphQL\Type\ExampleType::class,
                'CustomExample' => \App\GraphQL\Type\CustomExampleType::class
            ],

            'security' => [
                'query_max_complexity' => 1000,
                'query_max_depth' => 10,
            ],

        ]);
    }

    /**
     * Test that the service provider add types from config
     *
     * @test
     * @covers ::boot
     * @covers ::bootTypes
     */
    public function testBootTypes()
    {
        $types = GraphQL::getTypes();
        $this->assertEquals(config('graphql.types'), $types);
    }

    /**
     * Test that the service provider add schemas from config
     *
     * @test
     * @covers ::boot
     * @covers ::bootSchemas
     */
    public function testBootSchemas()
    {
        $schemas = GraphQL::getSchemas();
        $this->assertEquals(config('graphql.schemas'), $schemas);
    }

    /**
     * Test that the service provider set security config
     *
     * @test
     * @covers ::boot
     * @covers ::bootSecurity
     */
    public function testBootSecurity()
    {
        $value = GraphQL::getMaxQueryDepth();
        $this->assertEquals(config('graphql.security.query_max_depth'), $value);

        $value = GraphQL::getMaxQueryComplexity();
        $this->assertEquals(config('graphql.security.query_max_complexity'), $value);
    }

    /**
     * Test that the service provider set views
     *
     * @test
     * @covers ::boot
     * @covers ::bootViews
     */
    public function testBootViews()
    {
        $listeners = Event::getListeners('composing: '.config('graphql.graphiql.view'));
        $this->assertArrayHasKey(0, $listeners);
    }

    /**
     * Test that the service provider add routes
     *
     * @test
     * @covers ::boot
     * @covers ::bootRouter
     */
    public function testBootRouter()
    {
        $patterns = Route::getPatterns();
        $this->assertArrayHasKey('graphql_schema', $patterns);

        $queryRoute = Route::getRoutes()->getByName('graphql.query');
        $this->assertNotNull($queryRoute);
        $uri = config('graphql.routes_prefix').'/'.config('graphql.routes.query');
        $this->assertEquals($uri, $queryRoute->uri());

        $mutationRoute = Route::getRoutes()->getByName('graphql.mutation');
        $this->assertNotNull($mutationRoute);
        $uri = config('graphql.routes_prefix').'/'.config('graphql.routes.mutation');
        $this->assertEquals($uri, $mutationRoute->uri());

        $graphiqlRoute = Route::getRoutes()->getByName('graphql.graphiql');
        $this->assertNotNull($graphiqlRoute);
        $uri = ltrim(config('graphql.graphiql.routes'), '/');
        $this->assertEquals($uri, $graphiqlRoute->uri());
    }
}
