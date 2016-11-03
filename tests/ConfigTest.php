<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class ConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql', [
            
            'prefix' => 'graphql_test',
            
            'routes' => [
                'query' => 'query/{graphql_schema?}',
                'mutation' => 'mutation/{graphql_schema?}'
            ],
            
            'variables_input_name' => 'variables',
            
            'schema' => 'custom',
            
            'schemas' => [
                'default' => [
                    'query' => [
                        'examples' => ExamplesQuery::class,
                        'examplesContext' => ExamplesContextQuery::class,
                        'examplesRoot' => ExamplesRootQuery::class
                    ],
                    'mutation' => [
                        'updateExample' => UpdateExampleMutation::class
                    ]
                ],
                'custom' => [
                    'query' => [
                        'examplesCustom' => ExamplesQuery::class
                    ],
                    'mutation' => [
                        'updateExampleCustom' => UpdateExampleMutation::class
                    ]
                ]
            ],
            
            'types' => [
                'Example' => ExampleType::class,
                CustomExampleType::class
            ]
            
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
    
    public function testVariablesInputName()
    {
        $response = $this->call('GET', '/graphql_test/query/default', [
            'query' => $this->queries['examplesWithParams'],
            'variables' => [
                'index' => 0
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        $content = $response->getOriginalContent();
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($content['data'], [
            'examples' => [
                $this->data[0]
            ]
        ]);
    }
    
    public function testErrorFormatter()
    {
        $error = $this->getMockBuilder(ErrorFormatter::class)
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
