<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ExamplesContextQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples context query'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context)
    {
        return $context;
    }
}
