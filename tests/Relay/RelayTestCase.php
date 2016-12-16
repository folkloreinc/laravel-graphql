<?php

abstract class RelayTestCase extends TestCase
{
    protected $queries;
    protected $data;
    
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas.default', [
            'query' => [
                'example' => \App\GraphQL\Relay\Query\ExampleQuery::class,
                'node' => \Folklore\GraphQL\Relay\NodeQuery::class
            ],
            'mutation' => [
                'updateName' => \App\GraphQL\Relay\Mutation\UpdateNameMutation::class
            ]
        ]);
        
        $app['config']->set('graphql.types', [
            'Example' => \App\GraphQL\Type\ExampleType::class,
            'ExampleNode' => \App\GraphQL\Relay\Type\ExampleNodeType::class,
            'ExampleNodeTrait' => \App\GraphQL\Relay\Type\ExampleNodeTraitType::class,
            'ExampleItemsConnection' => \App\GraphQL\Relay\Type\ExampleItemsConnectionType::class,
            'ExampleItem' => \App\GraphQL\Relay\Type\ExampleItemType::class,
            'UpdateNamePayload' => \App\GraphQL\Relay\Type\UpdateNamePayloadType::class,
            'UpdateNameInput' => \App\GraphQL\Relay\Type\UpdateNameInputType::class,
            'Node' => \Folklore\GraphQL\Relay\NodeInterface::class,
            'PageInfo' => \Folklore\GraphQL\Relay\PageInfoType::class
        ]);
    }
}
