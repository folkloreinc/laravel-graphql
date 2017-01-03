<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\PayloadType;
use GraphQL;

class UpdateNamePayloadType extends PayloadType
{
    protected $attributes = [
        'name' => 'UpdateNamePayload',
        'description' => 'An example relay mutation payload'
    ];

    protected function fields()
    {
        return [
            'example' => [
                'type' => GraphQL::type('ExampleNode'),
                'description' => 'The id field'
            ]
        ];
    }
}
