<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use GraphQL;

class ExamplesRootQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples root query'
    ];
    
    protected function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context)
    {
        return $root;
    }
}
