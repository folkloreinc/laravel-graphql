<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use Illuminate\Pagination\LengthAwarePaginator;
use GraphQL;

class PaginationType extends ObjectType
{
    public function __construct($type)
    {
        parent::__construct([
            'name' => $type . 'Pagination',
            'fields' => [
                'items' => [
                    'type' => GraphQLType::listOf(GraphQL::type($type)),
                    'resolve' => function (LengthAwarePaginator $paginator) {
                        return $paginator->getCollection();
                    },
                ],
                'cursor' => [
                    'type' => GraphQLType::nonNull(GraphQL::type('PaginationCursor')),
                    'resolve' => function (LengthAwarePaginator $paginator) {
                        return $paginator;
                    },
                ],
            ],
        ]);
    }
}
