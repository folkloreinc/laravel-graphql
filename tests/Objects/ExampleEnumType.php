<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\EnumType as BaseEnumType;

class ExampleEnumType extends BaseEnumType
{
    protected $attributes = [
        'name' => 'ExampleEnum',
        'description' => 'An example enum'
    ];

    public function values()
    {
        return [
            'TEST' => [
                'value' => 1,
                'description' => 'test'
            ]
        ];
    }

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
