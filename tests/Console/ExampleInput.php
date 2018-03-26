<?php

namespace App\GraphQL\Inputs;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InputType;
use GraphQL;

class ExampleInput extends InputType
{
    protected $attributes = [
        'name' => 'ExampleInput',
        'description' => 'An input'
    ];

    public function fields()
    {
        return [

        ];
    }
}
