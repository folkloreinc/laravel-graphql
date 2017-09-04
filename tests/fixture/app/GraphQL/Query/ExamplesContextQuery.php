<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use GraphQL;

class ExamplesContextQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples context query'
    ];
    
    protected function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context)
    {
        return $context;
    }
}
