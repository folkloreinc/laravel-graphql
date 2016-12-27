<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InputType as BaseInputType;

class ExampleInputType extends BaseInputType
{
    protected $attributes = [
        'name' => 'ExampleInput',
        'description' => 'An example'
    ];

    public function fields()
    {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ]
        ];
    }
}
