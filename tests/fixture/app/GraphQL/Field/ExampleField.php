<?php

namespace App\GraphQL\Field;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field;

class ExampleField extends Field
{
    protected $attributes = [
        'name' => 'example'
    ];

    protected function type()
    {
        return Type::listOf(Type::string());
    }

    protected function args()
    {
        return [
            'index' => [
                'name' => 'index',
                'type' => Type::int()
            ]
        ];
    }

    public function resolve($root, $args)
    {
        return ['test'];
    }
}
