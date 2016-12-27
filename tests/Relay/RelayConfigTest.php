<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class RelayConfigTest extends RelayTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas', [
            'default' => [
                'query' => [
                    'example' => \App\GraphQL\Relay\Query\ExampleQuery::class
                ],
                'mutation' => [
                    'updateName' => \App\GraphQL\Relay\Mutation\UpdateNameMutation::class
                ]
            ],
            'custom' => [
                'query' => [
                    'example' => \App\GraphQL\Relay\Query\ExampleQuery::class,
                    'node' => \Folklore\GraphQL\Relay\NodeQuery::class
                ],
                'mutation' => [
                    'updateName' => \App\GraphQL\Relay\Mutation\UpdateNameMutation::class
                ]
            ]
        ]);
        
        $app['config']->set('graphql.relay', [
            'schemas' => '*',
            'query' => [
                'node' => \Folklore\GraphQL\Relay\NodeQuery::class
            ],
            'types' => [
                'Node' => \Folklore\GraphQL\Relay\NodeInterface::class,
                'PageInfo' => \Folklore\GraphQL\Relay\PageInfoType::class
            ]
        ]);
    }
    
    public function testConfigRelaySchema()
    {
        $schemas = GraphQL::getSchemas();
        $configQuery = config('graphql.relay.query');
        foreach ($schemas as $schema) {
            foreach ($configQuery as $key => $query) {
                $this->assertArrayHasKey($key, $schema['query']);
                $this->assertEquals($schema['query'][$key], $query);
            }
        }
    }
}
