<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ConnectionType extends BaseType
{
    protected $edgeType;
    
    public function edgeType()
    {
        return null;
    }
    
    public function getEdgeType()
    {
        $edgeType = $this->edgeType();
        return $edgeType ? $edgeType:$this->edgeType;
    }
    
    public function getEdgeObjectType()
    {
        $edgeType = $this->getEdgeType();
        $name = $edgeType->config['name'].'Edge';
        GraphQL::addType(\Folklore\GraphQL\Relay\ConnectionEdgeType::class, $name);
        return GraphQL::type($name)
            ->withEdgeType($edgeType);
    }
    
    public function getCursorFromEdge($edge)
    {
        $edgeType = $this->getEdgeType();
        $resolveId = $edgeType->getField('id')->resolveFn;
        return $resolveId($edge);
    }
    
    public function getEdgesFromRoot($root)
    {
        return array_map(function ($item) {
            return [
                'cursor' => $this->getCursorFromEdge($item),
                'node' => $item
            ];
        }, $root);
    }
    
    public function getPageInfoFromRoot($root)
    {
        $hasPreviousPage = false;
        $hasNextPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasPreviousPage = !$root->onFirstPage();
            $hasNextPage = $root->hasMorePages();
        } elseif ($root instanceof AbstractPaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        }
        
        $edges = $this->getEdgesFromRoot($root);
        
        return [
            'hasPreviousPage' => $hasPreviousPage,
            'hasNextPage' => $hasNextPage,
            'startCursor' => array_get($edges, '0.cursor'),
            'endCursor' => array_get($edges, (sizeof($edges)-1).'.cursor')
        ];
    }

    public function fields()
    {
        return [
            'edges' => [
                'type' => Type::listOf($this->getEdgeObjectType()),
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
