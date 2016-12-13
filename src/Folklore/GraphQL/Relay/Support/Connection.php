<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

abstract class Connection extends BaseType
{
    protected $attributes = [
        'name' => 'Connection',
        'description' => 'A relay connection'
    ];
    
    abstract public function edgeType();
    
    public function getEdgesFromRoot($root)
    {
        $edgeType = $this->edgeType();
        $name = $edgeType->config['name'];
        $resolveId = $edgeType->getField('id')->resolveFn;
        return array_map(function ($item) use ($resolveId) {
            return [
                'cursor' => $resolveId($item),
                'node' => $item
            ];
        }, $root);
    }
    
    public function getPageInfoFromRoot($root)
    {
        $edges = $this->getEdgesFromRoot($root);
        
        return [
            'hasPreviousPage' => false,
            'hasNextPage' => false,
            'startCursor' => array_get($edges, '0.cursor'),
            'endCursor' => array_get($edges, (sizeof($edges)-1).'.cursor')
        ];
    }
    
    public function getEdgeType()
    {
        $edgeType = $this->edgeType();
        $name = $edgeType->config['name'].'Edge';
        GraphQL::addType(\App\GraphQL\Relay\ConnectionEdgeType::class, $name);
        return GraphQL::type($name)
            ->withEdgeType($edgeType);
    }

    public function fields()
    {
        return [
            'edges' => [
                'type' => Type::listOf($this->getEdgeType()),
                'resolve' => function ($root) {
                    return $this->getEdgesFromRoot($root);
                }
            ],
            'pageInfo' => [
                'type' => GraphQL::type('PageInfo'),
                'resolve' => function ($root) {
                    return $this->getPageInfoFromRoot($root);
                }
            ]
        ];
    }
}
