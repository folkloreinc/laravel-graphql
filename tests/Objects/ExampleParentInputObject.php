<?php

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL\Type\Definition\Type;

class ExampleParentInputObject extends BaseType
{
    protected $inputObject = true;

    protected $attributes = [
        'name' => 'ExampleParentInputObject',
    ];

    public function type()
    {
        return Type::listOf(Type::string());
    }

    public function fields()
    {
        return [

            'alpha' => [
                'name' => 'alpha',
                'type' => Type::nonNull(Type::string()),
                'rules' => ['alpha'],
            ],

            'child' => [
                'name' => 'child',
                'type' => GraphQL::type('ExampleChildInputObject'),
            ],

            'child-list' => [
                'name' => 'child-list',
                'type' => Type::listOf(GraphQL::type('ExampleChildInputObject')),
            ],

            // Reference itself. Used in test for avoiding infinite loop when creating validation rules
            'self' => [
                'name' => 'self',
                'type' => GraphQL::type('ExampleParentInputObject'),
            ],

        ];
    }

    public function resolve($root, $args)
    {
        return ['test'];
    }
}
