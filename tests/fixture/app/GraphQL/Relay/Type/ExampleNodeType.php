<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\NodeType as BaseNodeType;
use GraphQL;

use App\Data;

class ExampleNodeType extends BaseNodeType
{
    protected $attributes = [
        'name' => 'ExampleNode',
        'description' => 'An example relay node'
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
            ],
            'items' => \App\GraphQL\Relay\Field\ExampleItemsField::class
        ];
    }
    
    public function resolveById($id)
    {
        return Data::getById($id);
    }
}
