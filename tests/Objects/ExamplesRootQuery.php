<?php

use Folklore\GraphQL\Support\Query;
use GraphQL\Type\Definition\ResolveInfo;

class ExamplesRootQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples root query'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return $root;
    }
}
