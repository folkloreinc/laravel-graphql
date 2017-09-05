<?php

namespace App\GraphQL\Type;

use Folklore\GraphQL\Support\UnionType;
use GraphQL;

class ExampleUnionType extends UnionType
{
    protected function attributes()
    {
        return [
            'name' => 'name',
            'description' => 'description'
        ];
    }

    protected function types()
    {
        return [
            GraphQL::type('Example')
        ];
    }
}
