<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\Type as NodeType;
use GraphQL;

use App\Data;

class ExampleItemType extends NodeType
{
    protected $attributes = [
        'name' => 'ExampleItem',
        'description' => 'An example item'
    ];

    public function fields()
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
    
    public function resolveById($id)
    {
        return Data::getById($id);
    }
}
