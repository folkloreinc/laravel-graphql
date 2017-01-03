<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field as BaseField;
use Folklore\GraphQL\Relay\Support\Traits\ResolvesFromQueryBuilder;
use GraphQL;

class ConnectionField extends BaseField
{
    use ResolvesFromQueryBuilder;
    
    protected function args()
    {
        return [
            'first' => [
                'name' => 'first',
                'type' => Type::int()
            ],
            'last' => [
                'name' => 'last',
                'type' => Type::int()
            ],
            'after' => [
                'name' => 'after',
                'type' => Type::id()
            ],
            'before' => [
                'name' => 'before',
                'type' => Type::id()
            ]
        ];
    }
}
