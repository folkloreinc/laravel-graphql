<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class CustomExampleType extends GraphQLType
{

    protected $attributes = [
        'name' => 'CustomExample',
        'description' => 'An example'
    ];

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
