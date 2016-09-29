<?php

namespace Folklore\GraphQL\Tests\Objects;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field;

class ExampleField extends Field
{
    protected $attributes = [
        'name' => 'example'
    ];
    
    public function type()
    {
        return Type::listOf(Type::string());
    }

    public function args()
    {
        return [];
    }

    public function resolve($root, $args)
    {
        return;
    }
}
