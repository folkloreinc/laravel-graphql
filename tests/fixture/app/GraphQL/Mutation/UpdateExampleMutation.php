<?php

namespace App\GraphQL\Mutation;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation;
use GraphQL;

class UpdateExampleMutation extends Mutation
{
    
    protected $attributes = [
        'name' => 'updateExample'
    ];
    
    public function type()
    {
        return GraphQL::type('Example');
    }
    
    public function rules()
    {
        return [
            'name' => ['required']
        ];
    }

    public function args()
    {
        return [
            'name' => [
                'name' => 'name',
                'type' => Type::string()
            ],
            
            'name_with_rules' => [
                'name' => 'name',
                'type' => Type::string(),
                'rules' => ['required']
            ],
            
            'name_with_rules_closure' => [
                'name' => 'name',
                'type' => Type::string(),
                'rules' => function () {
                    return ['required'];
                }
            ]
        ];
    }

    public function resolve($root, $args)
    {
        return [
            'name' => array_get($args, 'name')
        ];
    }
}
