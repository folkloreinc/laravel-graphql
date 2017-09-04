<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\InputType;
use GraphQL;

class UpdateNameInputType extends InputType
{
    protected $attributes = [
        'name' => 'UpdateNameInput',
        'description' => 'An example relay mutation input'
    ];

    protected function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id field'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ]
        ];
    }
}
