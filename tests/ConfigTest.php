<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class ConfigTest extends TestCase
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
                \App\GraphQL\Type\CustomExampleType::class
            ],

            'security' => [
                'query_max_complexity' => 1000,
                'query_max_depth' => 10,
            ],

        ]);
    }

    public function testRouteQuery()
    {
        $response = $this->call('GET', '/graphql_test/query', [
            'query' => $this->queries['examplesCustom']
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
    }

    public function testRouteMutation()
    {
        $response = $this->call('POST', '/graphql_test/mutation', [
            'query' => $this->queries['updateExampleCustom']
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
    }
    
    public function testTypes()
    {
        $types = GraphQL::getTypes();
        $this->assertArrayHasKey('Example', $types);
        $this->assertArrayHasKey('CustomExample', $types);
    }
    
    public function testSchema()
    {
        $schema = GraphQL::schema();
        $schemaCustom = GraphQL::schema('custom');

        $this->assertEquals($schema, $schemaCustom);
    }

    public function testSchemas()
    {
        $schemas = GraphQL::getSchemas();

        $this->assertArrayHasKey('default', $schemas);
        $this->assertArrayHasKey('custom', $schemas);
    }

    public function testRequestVariablesName()
    {
        $response = $this->call('GET', '/graphql_test/query/default', [
            'query' => $this->queries['examplesWithParams'],
            'params' => [
                'id' => 1
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examples' => [
                \App\Data::getById(1)
            ]
        ]);
    }

    public function testSecurity()
    {
        $queryComplexity = DocumentValidator::getRule('QueryComplexity');
        $this->assertEquals(1000, $queryComplexity->getMaxQueryComplexity());

        $queryDepth = DocumentValidator::getRule('QueryDepth');
        $this->assertEquals(10, $queryDepth->getMaxQueryDepth());
    }

    public function testErrorFormatter()
    {
        $error = $this->getMockBuilder(\App\ErrorFormatter::class)
                    ->setMethods(['formatError'])
                    ->getMock();

        $error->expects($this->once())
            ->method('formatError');

        config([
            'graphql.error_formatter' => [$error, 'formatError']
        ]);

        $result = GraphQL::query($this->queries['examplesWithError']);
    }
}
