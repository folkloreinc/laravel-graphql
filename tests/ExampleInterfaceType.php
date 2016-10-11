<?php

namespace Folklore\GraphQL\Tests;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InterfaceType as GraphQLInterfaceType;

class ExampleInterfaceType extends GraphQLInterfaceType {

    protected $attributes = [
        'name' => 'ExampleInterface',
        'description' => 'An example interface'
    ];

    public function fields()
    {
        return [
            'test' => [
                'type' => Type::string(),
                'description' => 'A test field'
            ]
        ];
    }

}
