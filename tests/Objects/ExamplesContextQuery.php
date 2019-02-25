<?php

use Folklore\GraphQL\Support\Query;
use GraphQL\Type\Definition\ResolveInfo;

class ExamplesContextQuery extends Query
{
    
    protected $attributes = [
        'name' => 'Examples context query'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return $context;
    }
}
