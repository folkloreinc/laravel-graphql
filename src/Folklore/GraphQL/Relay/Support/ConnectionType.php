<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\EdgesCollection;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ConnectionType extends BaseType
{
    /**
     * @var mixed
     */
    protected $edgeType;

    protected function edgeType()
    {
        return null;
    }

    /**
     * @return mixed
     */
    protected function fields()
    {
        return [
            'total'    => [
                'type'    => Type::int(),
                'resolve' => function ($root) {
                    return $this->getTotalFromRoot($root);
                },
            ],
            'edges'    => [
                'type'    => Type::listOf($this->getEdgeObjectType()),
                'resolve' => function ($root) {
                    return $this->getEdgesFromRoot($root);
                },
            ],
            'pageInfo' => [
                'type'    => app('graphql')->type('PageInfo'),
                'resolve' => function ($root) {
                    return $this->getPageInfoFromRoot($root);
                },
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function getEdgeType()
    {
        $edgeType = $this->edgeType();
        return $edgeType ? $edgeType : $this->edgeType;
    }

    /**
     * @param $edgeType
     * @return mixed
     */
    public function setEdgeType($edgeType)
    {
        $this->edgeType = $edgeType;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getEdgeObjectType()
    {
        $edgeType = $this->getEdgeType();
        $name     = $edgeType->config['name'].'Edge';
        app('graphql')->addType(\Folklore\GraphQL\Relay\ConnectionEdgeType::class, $name);
        $type = app('graphql')->type($name);
        $type->setEdgeType($edgeType);
        return $type;
    }

    /**
     * @param $edge
     * @return mixed
     */
    protected function getCursorFromNode($edge)
    {
        $edgeType = $this->getEdgeType();
        if ($edgeType instanceof InterfaceType) {
            $edgeType = $edgeType->config['resolveType']($edge);
        }
        $resolveId = $edgeType->getField('id')->resolveFn;
        return $resolveId($edge);
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getTotalFromRoot($root)
    {
        $total = 0;
        if ($root instanceof EdgesCollection) {
            $total = $root->getTotal();
        }
        return $total;
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getEdgesFromRoot($root)
    {
        $cursor = $this->getStartCursorFromRoot($root);
        $edges  = [];
        foreach ($root as $item) {
            $edges[] = [
                'cursor' => $cursor !== null ? $cursor : $this->getCursorFromNode($item),
                'node'   => $item,
            ];
            if ($cursor !== null) {
                $cursor++;
            }
        }
        return $edges;
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getHasPreviousPageFromRoot($root)
    {
        $hasPreviousPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        } elseif ($root instanceof AbstractPaginator) {
            $hasPreviousPage = !$root->onFirstPage();
        } elseif ($root instanceof EdgesCollection) {
            $hasPreviousPage = $root->getHasPreviousPage();
        }

        return $hasPreviousPage;
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getHasNextPageFromRoot($root)
    {
        $hasNextPage = false;
        if ($root instanceof LengthAwarePaginator) {
            $hasNextPage = $root->hasMorePages();
        } elseif ($root instanceof EdgesCollection) {
            $hasNextPage = $root->getHasNextPage();
        }

        return $hasNextPage;
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getStartCursorFromRoot($root)
    {
        $startCursor = null;
        if ($root instanceof EdgesCollection) {
            $startCursor = $root->getStartCursor();
        }

        return $startCursor;
    }

    /**
     * @param $root
     * @return mixed
     */
    protected function getEndCursorFromRoot($root)
    {
        $endCursor = null;
        if ($root instanceof EdgesCollection) {
            $endCursor = $root->getEndCursor();
        }

        return $endCursor;
    }

    /**
     * @param $root
     */
    protected function getPageInfoFromRoot($root)
    {
        $hasPreviousPage = $this->getHasPreviousPageFromRoot($root);
        $hasNextPage     = $this->getHasNextPageFromRoot($root);
        $startCursor     = $this->getStartCursorFromRoot($root);
        $endCursor       = $this->getEndCursorFromRoot($root);
        $edges           = $startCursor === null || $endCursor === null ? $this->getEdgesFromRoot($root) : null;

        return [
            'hasPreviousPage' => $hasPreviousPage,
            'hasNextPage'     => $hasNextPage,
            'startCursor'     => $startCursor !== null ? $startCursor : array_get($edges, '0.cursor'),
            'endCursor'       => $endCursor !== null ? $endCursor : array_get($edges, (sizeof($edges) - 1).'.cursor'),
        ];
    }
}
