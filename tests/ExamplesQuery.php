<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query; 

class ExamplesQuery extends Query {
    
    protected $attributes = [
        'name' => 'Examples query'
    ];
    
    public function type()
    {
        return Type::listOf(GraphQL::type('Example'));
    }

    public function args()
    {
        return [
            'test' => ['name' => 'test', 'type' => Type::string()]
        ];
    }

    public function resolve($root, $args)
    {
        return [
            [
                'test' => 'Example 1'
            ],
            [
                'test' => 'Example 2'
            ],
            [
                'test' => 'Example 3'
            ]
        ];
    }
    
}
