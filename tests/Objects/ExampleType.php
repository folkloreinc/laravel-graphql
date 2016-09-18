<?php

namespace Folklore\GraphQL\Tests\Objects;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class ExampleType extends GraphQLType {

    protected $attributes = [
        'name' => 'Example',
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
