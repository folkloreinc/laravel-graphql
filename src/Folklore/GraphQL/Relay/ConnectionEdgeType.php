<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;

class ConnectionEdgeType extends BaseType
{
    protected function fields()
    {
        return [
            'cursor' => [
                'type' => Type::nonNull(Type::id())
            ],
            'node' => [
                'type' => app('graphql')->type('Node')
            ]
        ];
    }
    
    public function toType()
    {
        return new EdgeObjectType($this->toArray());
    }
}
