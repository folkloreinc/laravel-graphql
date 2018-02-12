<?php

abstract class RelayTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.schemas.default', [
            'query' => [
                'example' => \App\GraphQL\Relay\Query\ExampleQuery::class
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
            'UpdateNameInput' => \App\GraphQL\Relay\Type\UpdateNameInputType::class
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Folklore\GraphQL\ServiceProvider::class,
            \Folklore\GraphQL\Relay\ServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'GraphQL' => \Folklore\GraphQL\Support\Facades\GraphQL::class,
            'Relay' => \Folklore\GraphQL\Relay\Support\Facades\Relay::class
        ];
    }
}
