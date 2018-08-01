<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class AnotherCustomExampleType extends GraphQLType
{

    protected $attributes = [
        'name' => 'AnotherCustomExample',
        'description' => 'An example'
    ];

    public function fields()
    {
        return [
            'test' => [
                'type' => Type::string(),
                'description' => 'A test field'
            ]
        ];
    }
}
