<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use Illuminate\Pagination\LengthAwarePaginator;
use GraphQL;

class PaginationType extends ObjectType
{
    public function __construct($typeName)
    {
        parent::__construct([
            'name'  => $typeName . 'Pagination',
            'fields' => [
                'data' => [
                    'type' => GraphQLType::listOf(GraphQL::type($typeName)),
                    'resolve' => function (LengthAwarePaginator $data) {
                        return $data->getCollection();
                    },
                ],
                'total' => [
                    'type' => GraphQLType::nonNull(GraphQLType::int()),
                    'description' => 'Number of total items selected by the query',
                    'resolve' => function (LengthAwarePaginator $data) {
                        return $data->total();
                    },
                    'selectable' => false,
                ],
                'limit' => [
                    'type' => GraphQLType::nonNull(GraphQLType::int()),
                    'description' => 'Number of items returned per page',
                    'resolve' => function (LengthAwarePaginator $data) {
                        return $data->perPage();
                    },
                    'selectable' => false,
                ],
                'page' => [
                    'type' => GraphQLType::nonNull(GraphQLType::int()),
                    'description' => 'Current page of the cursor',
                    'resolve' => function (LengthAwarePaginator $data) {
                        return $data->currentPage();
                    },
                    'selectable' => false,
                ],
            ],
        ]);
    }
}
