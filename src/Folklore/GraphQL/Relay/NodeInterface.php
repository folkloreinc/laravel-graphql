<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Relay\Exception\NodeRootInvalid;
use Folklore\GraphQL\Support\InterfaceType as BaseInterfaceType;
use GraphQL\Type\Definition\Type;

class NodeInterface extends BaseInterfaceType
{
    /**
     * @var array
     */
    protected $attributes = [
        'name'        => 'Node',
        'description' => 'The relay node interface',
    ];

    protected function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
        ];
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function resolveType($root)
    {
        if (!$root instanceof NodeResponse) {
            throw new NodeRootInvalid('$root is not a NodeResponse');
        }
        return $root->getType();
    }
}
