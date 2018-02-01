<?php

use Folklore\GraphQL\Support\Facades\GraphQL;
use Folklore\GraphQL\Support\Mutation;
use GraphQL\Type\Definition\Type;

class UpdateExampleMutationWithInputType extends Mutation
{
    protected $attributes = [
        'name' => 'updateExample',
    ];

    public function type()
    {
        return GraphQL::type('Example');
    }

    public function rules()
    {
        return [
            'test' => ['required'],
        ];
    }

    public function validationErrorMessages($root, $args, $context)
    {
        $inavlidEmail = array_get($args, 'test_with_rules_input_object.nest.email');

        return [
             'test.required' => 'A test is required.',
             'test_with_rules_input_object.nest.email.email' => 'Invalid your email : '.$inavlidEmail,
          ];
    }

    public function args()
    {
        return [
            'test' => [
                'name' => 'test',
                'type' => Type::string(),
            ],

            'test_with_rules' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => ['required'],
            ],

            'test_with_rules_closure' => [
                'name' => 'test',
                'type' => Type::string(),
                'rules' => function () {
                    return ['required'];
                },
            ],

            'test_with_rules_input_object' => [
                'name' => 'test',
                'type' => GraphQL::type('ExampleValidationInputObject'),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return [
            'test' => array_get($args, 'test'),
        ];
    }
}
