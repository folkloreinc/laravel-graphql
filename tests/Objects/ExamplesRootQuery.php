<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ExamplesRootQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples root query'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context)
    {
        return $root;
    }
}
