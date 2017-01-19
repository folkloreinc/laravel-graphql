<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

class PageInfoType extends BaseType
{
    protected $attributes = [
        'name' => 'PageInfo',
        'description' => 'The relay pageInfo type used by connections'
    ];

    protected function fields()
    {
        return [
            'hasNextPage' => [
                'type' => Type::nonNull(Type::boolean())
            ],
            'hasPreviousPage' => [
                'type' => Type::nonNull(Type::boolean())
            ],
            'startCursor' => [
                'type' => Type::string()
            ],
            'endCursor' => [
                'type' => Type::string()
            ]
        ];
    }
}
