<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\Type;

class PageInfoType extends BaseType
{
    /**
     * @var array
     */
    protected $attributes = [
        'name'        => 'PageInfo',
        'description' => 'The relay pageInfo type used by connections',
    ];

    protected function fields()
    {
        return [
            'hasNextPage'     => [
                'type' => Type::nonNull(Type::boolean()),
            ],
            'hasPreviousPage' => [
                'type' => Type::nonNull(Type::boolean()),
            ],
            'startCursor'     => [
                'type' => Type::string(),
            ],
            'endCursor'       => [
                'type' => Type::string(),
            ],
        ];
    }
}
