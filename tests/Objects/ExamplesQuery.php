<?php

use Folklore\GraphQL\Support\Query;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class ExamplesQuery extends Query
{
    protected $attributes = [
        'name' => 'examples'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Example'));
    }

    public function args()
    {
        return [
            'index' => [
                'name' => 'index',
                'type' => Type::int(),
                'rules' => ['integer', 'max:100']
            ]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $data = include(__DIR__ . '/data.php');

        if (isset($args['index'])) {
            return [
                $data[$args['index']]
            ];
        }

        return $data;
    }
}
