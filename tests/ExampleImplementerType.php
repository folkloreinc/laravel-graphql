<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class ExampleImplementerType extends GraphQLType {

    protected $attributes = [
        'name' => 'ExampleImplementer',
        'description' => 'An example of a type that implements an interface'
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

    public function interfaces()
    {
        return [
            GraphQL::type('ExampleInterface')
        ];
    }

}
