<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use GraphQL;

use App\Data;

class ExamplesQuery extends Query
{
    
    protected $attributes = [
        'name' => 'examples'
    ];
    
    public function type()
    {
        return Type::listOf(GraphQL::type('Example'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::id()]
        ];
    }

    public function resolve($root, $args)
    {
        if (isset($args['id'])) {
            return [
                Data::getById($args['id'])
            ];
        }
        
        return Data::get();
    }
}
