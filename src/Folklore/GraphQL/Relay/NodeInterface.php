<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\InterfaceType as BaseInterfaceType;
use Folklore\GraphQL\Relay\Exception\NodeRootInvalid;

class NodeInterface extends BaseInterfaceType
{
    protected $attributes = [
        'name' => 'Node',
        'description' => 'The relay node interface'
    ];

    protected function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id())
            ]
        ];
    }
    
    protected function resolveType($root)
    {
        if (!$root instanceof NodeResponse) {
            throw new NodeRootInvalid('$root is not a NodeResponse');
        }
        return $root->getType();
    }
}
