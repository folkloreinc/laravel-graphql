<?php

use Folklore\GraphQL\Support\Field;
use GraphQL\Type\Definition\Type;

class ExampleValidationField extends Field
{
    protected $attributes = [
        'name' => 'example_validation'
    ];
    
    public function type()
    {
        return Type::listOf(Type::string());
    }

    public function args()
    {
        return [
            'index' => [
                'name' => 'index',
                'type' => Type::int(),
                'rules' => ['integer', 'max:100']
            ]
        ];
    }
}
