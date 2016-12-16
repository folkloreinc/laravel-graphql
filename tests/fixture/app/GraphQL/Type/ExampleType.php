<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class ExampleType extends GraphQLType
{

    protected $attributes = [
        'name' => 'Example',
        'description' => 'An example'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::id(),
                'description' => 'The id field'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ],
            'name_method' => [
                'type' => Type::string(),
                'description' => 'The name method field'
            ],
            'name_validation' => \App\GraphQL\Field\ExampleValidationField::class
        ];
    }
    
    public function resolveNameMethodField()
    {
        return null;
    }
}
