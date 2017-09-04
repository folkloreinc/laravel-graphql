<?php

namespace App\GraphQL\Mutation;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Folklore\GraphQL\Support\Mutation;
use GraphQL;

class UpdateExampleMutation extends Mutation
{
    
    protected $attributes = [
        'name' => 'updateExample'
    ];
    
    protected function type()
    {
        return GraphQL::type('Example');
    }

    protected function args()
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
                'rules' => function ($root, $args, $context) {
                    return ['required'];
                }
            ]
        ];
    }
    
    protected function rules($root, $args, $context)
    {
        return [
            'name' => ['required']
        ];
    }

    public function resolve($root, $args)
    {
        return [
            'name' => array_get($args, 'name')
        ];
    }
}
