<?php

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL\Type\Definition\Type;

class ExampleChildInputObject extends BaseType
{
    protected $inputObject = true;

    protected $attributes = [
        'name' => 'ExampleChildInputObject'
    ];

    public function fields()
    {
        return [
            'email' => [
                'name' => 'email',
                'type' => Type::string(),
                'rules' => ['email']
            ],
        ];
    }
}
