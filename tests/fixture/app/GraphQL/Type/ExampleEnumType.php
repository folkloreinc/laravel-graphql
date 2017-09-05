<?php

namespace App\GraphQL\Type;

use Folklore\GraphQL\Support\EnumType;

class ExampleEnumType extends EnumType
{
    protected function attributes()
    {
        return [
            'name' => 'name',
            'description' => 'description'
        ];
    }

    protected function values()
    {
        return [
            'TEST' => [
                'value' => 1
            ]
        ];
    }
}
