<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\ConnectionType;
use GraphQL;

use App\Data;

class ExampleItemsConnectionType extends ConnectionType
{
    protected $attributes = [
        'name' => 'ExampleItemsConnection',
        'description' => 'An example items connection'
    ];
    
    protected function edgeType()
    {
        return GraphQL::type('ExampleItem');
    }
}
