<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InterfaceType;

class ExampleInterfaceType extends InterfaceType
{

    protected $attributes = [
        'name' => 'ExampleInterface',
        'description' => 'An example interface'
    ];
    
    protected function resolveType($root)
    {
        return Type::string();
    }

    protected function fields()
    {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ]
        ];
    }
}
