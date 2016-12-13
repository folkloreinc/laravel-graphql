<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InterfaceType as BaseInterfaceType;

class NodeInterface extends BaseInterfaceType
{
    protected $attributes = [
        'name' => 'Node',
        'description' => 'The relay node interface'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id())
            ]
        ];
    }
    
    public function resolveType($root)
    {
        if ($root instanceof NodeResponse) {
            return $root->getType();
        }
        return Type::null();
    }
}
