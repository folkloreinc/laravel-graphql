<?php

namespace App\GraphQL\Relay\Mutation;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\Mutation;
use GraphQL;
use Relay;
use App\Data;

class UpdateNameMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateName'
    ];
    
    public function inputType()
    {
        return GraphQL::type('UpdateNameInput');
    }
    
    public function type()
    {
        return GraphQL::type('UpdateNamePayload');
    }

    public function resolve($root, $args)
    {
        $globalId = array_get($args, 'input.id');
        $id = Relay::getIdFromGlobalId($globalId);
        $node = Data::getById($id);
        $name = array_get($args, 'input.name');
        
        return [
            'example' => [
                'id' => $node['id'],
                'name' => $name
            ]
        ];
    }
}
