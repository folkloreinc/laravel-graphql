<?php

namespace Folklore\GraphQL\Tests\Objects;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation; 

class UpdateExampleMutation extends Mutation {
    
    protected $attributes = [
        'name' => 'updateExample'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
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
            'test' => array_get($args, 'test')
        ];
    }
    
}
