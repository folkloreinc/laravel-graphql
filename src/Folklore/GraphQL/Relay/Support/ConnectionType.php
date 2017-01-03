<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

use Folklore\GraphQL\Relay\EdgesCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ConnectionType extends BaseType
{
    protected $edgeType;
    
    protected function edgeType()
    {
        return null;
    }

    protected function fields()
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
    
    public function getEdgeType()
    {
        $edgeType = $this->edgeType();
        return $edgeType ? $edgeType:$this->edgeType;
    }
    
    public function setEdgeType($edgeType)
    {
        $this->edgeType = $edgeType;
        return $this;
    }
    
    protected function getEdgeObjectType()
    {
        $edgeType = $this->getEdgeType();
        $name = $edgeType->config['name'].'Edge';
        GraphQL::addType(\Folklore\GraphQL\Relay\ConnectionEdgeType::class, $name);
        $type = GraphQL::type($name);
        $type->setEdgeType($edgeType);
        return $type;
    }
    
    protected function getCursorFromEdge($edge)
    {
        $edgeType = $this->getEdgeType();
        if ($edgeType instanceof InterfaceType) {
            $edgeType = $edgeType->config['resolveType']($edge);
        }
        $resolveId = $edgeType->getField('id')->resolveFn;
        return $resolveId($edge);
    }
    
    protected function getEdgesFromRoot($root)
    {
        $edges = [];
        foreach ($root as $item) {
            $edges[] = [
                'cursor' => $this->getCursorFromEdge($item),
                'node' => $item
            ];
        }
        return $edges;
    }
    
    protected function getPageInfoFromRoot($root)
    {
        $hasPreviousPage = false;
        $hasNextPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasPreviousPage = !$root->onFirstPage();
            $hasNextPage = $root->hasMorePages();
        } elseif ($root instanceof AbstractPaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        } elseif ($root instanceof EdgesCollection) {
            $hasPreviousPage = $root->getHasPreviousPage();
            $hasNextPage = $root->getHasNextPage();
        }
        
        $edges = $this->getEdgesFromRoot($root);
        
        return [
            'hasPreviousPage' => $hasPreviousPage,
            'hasNextPage' => $hasNextPage,
            'startCursor' => array_get($edges, '0.cursor'),
            'endCursor' => array_get($edges, (sizeof($edges)-1).'.cursor')
        ];
    }
}
