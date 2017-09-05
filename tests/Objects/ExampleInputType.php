<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InputType as BaseInputType;

class ExampleInputType extends BaseInputType
{

    protected $attributes = [
        'name' => 'ExampleInput',
        'description' => 'An example input'
    ];

    public function fields()
    {
        return [
            'test' => [
                'type' => Type::string(),
                'description' => 'A test field'
            ],
            'test_validation' => ExampleValidationField::class
        ];
    }
}
