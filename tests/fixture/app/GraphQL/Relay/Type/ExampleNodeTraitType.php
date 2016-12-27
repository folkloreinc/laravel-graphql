<?php

namespace App\GraphQL\Relay\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use Folklore\GraphQL\Relay\Support\Traits\TypeIsNode;
use Folklore\GraphQL\Relay\Support\NodeContract;
use GraphQL;

use App\Data;

class ExampleNodeTraitType extends BaseType implements NodeContract
{
    use TypeIsNode;
    
    protected $attributes = [
        'name' => 'ExampleNodeTrait',
        'description' => 'An example relay node using the trait'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id field'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'The name field'
            ]
        ];
    }
    
    public function resolveById($id)
    {
        return Data::getById($id);
    }
}
