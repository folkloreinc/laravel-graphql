<?php

use Folklore\GraphQL\Support\Field;
use GraphQL\Type\Definition\Type;

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
        return [
            'index' => [
                'name' => 'index',
                'type' => Type::int()
            ]
        ];
    }
}
