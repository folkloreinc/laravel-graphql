<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\NodeType as BaseNodeType;
use GraphQL;
use Relay;

use App\Data;

class ExampleNodeType extends BaseNodeType
{
    protected $attributes = [
        'name' => 'ExampleNode',
        'description' => 'An example relay node'
    ];

    protected function fields()
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
            
            'items' => \App\GraphQL\Relay\Field\ExampleItemsField::class,
            
            'items_from_factory' => Relay::connectionField([
                'type' => GraphQL::type('ExampleItemsConnection'),
                'description' => 'An example connection field from facade method'
            ]),
            
            'items_from_edge_type_factory' => Relay::connectionFieldFromEdgeType(GraphQL::type('ExampleItem'), [
                'description' => 'An example connection field from facade method'
            ])
        ];
    }
    
    public function resolveById($id)
    {
        return Data::getById($id);
    }
}
