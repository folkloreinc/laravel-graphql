<?php

use Folklore\GraphQL\Support\Facades\GraphQL;
use Folklore\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class ExampleMutation extends Mutation
{
    protected $attributes = [
        'name' => 'exampleMutation'
    ];

    public function type()
    {
        return GraphQL::type('Example');
    }

    public function args()
    {
        return [
            'required' => [
                'name' => 'required',
                // Define required args through GraphQL types instead of Laravel validation
                // This way graphql-php takes care of validating that and the requirements
                // show up in the schema.
                'type' => Type::nonNull(Type::string()),
            ],

            'email_seperate_rules' => [
                'name' => 'email_seperate_rules',
                'type' => Type::string()
            ],

            'email_inline_rules' => [
                'name' => 'email_inline_rules',
                'type' => Type::string(),
                'rules' => ['email']
            ],

            'email_closure_rules' => [
                'name' => 'email_closure_rules',
                'type' => Type::string(),
                'rules' => function () {
                    return ['email'];
                }
            ],

            'email_list' => [
                'name' => 'email_list',
                'type' => Type::listOf(Type::string()),
                'rules' => ['email'],
            ],

            'email_list_of_lists' => [
                'name' => 'email_list_of_lists',
                'type' => Type::listOf(Type::listOf(Type::string())),
                'rules' => ['email'],
            ],

            'input_object' => [
                'name' => 'input_object',
                'type' => GraphQL::type('ExampleParentInputObject'),
            ],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return [
            'test' => array_get($args, 'test')
        ];
    }

    protected function rules()
    {
        return [
            'email_seperate_rules' => ['email']
        ];
    }

    protected function validationErrorMessages($root, $args, $context)
    {
        $invalidEmail = array_get($args, 'input_object.child.email');

        return [
            'email_inline_rules.email' => 'Has to be a valid email.',
            'input_object.child.email.email' => 'Invalid email: ' . $invalidEmail,
        ];
    }
}
