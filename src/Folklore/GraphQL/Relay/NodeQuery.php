<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Support\Query;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Exception\TypeNotFound;
use Folklore\GraphQL\Relay\Exception\NodeInvalid;

use Folklore\GraphQL\Relay\Support\NodeContract;

class NodeQuery extends Query
{
    protected $attributes = [
        'name' => 'NodeQuery',
        'description' => 'A query'
    ];

    protected function type()
    {
        return app('graphql')->type('Node');
    }


    protected function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::id())
            ]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $globalId = app('graphql.relay')->fromGlobalId($args['id']);
        $typeName = $globalId['type'];
        $id = $globalId['id'];
        $types = app('graphql')->getTypes();
        $typeClass = array_get($types, $typeName);

        if (!$typeClass) {
            throw new TypeNotFound('Type "'.$typeName.'" not found.');
        }

        $type = app($typeClass);

        if (!$type instanceof NodeContract) {
            throw new NodeInvalid('Type "'.$typeName.'" doesn\'t implement the NodeContract interface.');
        }

        $node = $type->resolveById($id);

        $response = new NodeResponse();
        $response->setNode($node);
        $response->setType(app('graphql')->type($typeName));

        return $response;
    }
}
