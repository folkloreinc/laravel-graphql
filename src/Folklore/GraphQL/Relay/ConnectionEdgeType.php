<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL\Type\Definition\Type;

class ConnectionEdgeType extends BaseType
{
    public function fields()
    {
        return [
            'cursor' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'node'   => [
                'type' => app('graphql')->type('Node'),
            ],
        ];
    }

    public function toType()
    {
        return new EdgeObjectType($this->toArray());
    }
}
