<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use GraphQL;
use Relay;
use Folklore\GraphQL\Relay\EdgesCollection;

trait ResolvesFromQueryBuilder
{
    protected $queryBuilderResolver;

    public function getQueryBuilderResolver()
    {
        return $this->queryBuilderResolver;
    }

    public function setQueryBuilderResolver($queryBuilderResolver)
    {
        $this->queryBuilderResolver = $queryBuilderResolver;
        return $queryBuilderResolver;
    }

    protected function scopeAfter($query, $id)
    {
        $query->where('id', '>=', $id);
    }

    protected function scopeBefore($query, $id)
    {
        $query->where('id', '<=', $id);
    }

    protected function scopeFirst($query, $value)
    {
        $query->orderBy('id', 'ASC');
        $query->take($value);
    }

    protected function scopeLast($query, $value)
    {
        $query->orderBy('id', 'DESC');
        $query->take($value);
    }

    protected function getCountFromQuery($query)
    {
        return $query->count();
    }

    protected function resolveQueryBuilderFromRoot($root, $args)
    {
        if (method_exists($this, 'resolveQueryBuilder')) {
            $queryBuilderResolver = [$this, 'resolveQueryBuilder'];
        } else {
            $queryBuilderResolver = $this->getQueryBuilderResolver();
        }

        if (!$queryBuilderResolver) {
            return null;
        }

        $args = func_get_args();
        return call_user_func_array($queryBuilderResolver, $args);
    }

    protected function resolveItemsFromQueryBuilder($query)
    {
        return $query->get();
    }

    protected function getCollectionFromItems($items, $offset, $limit, $hasPreviousPage, $hasNextPage)
    {
        $collection = new EdgesCollection($items);
        $collection->setStartCursor($offset);
        $collection->setEndCursor($offset + $limit - 1);
        $collection->setHasNextPage($hasNextPage);
        $collection->setHasPreviousPage($hasPreviousPage);
        return $collection;
    }

    public function resolve($root, $args)
    {
        // Get the query builder
        $arguments = func_get_args();
        $query = call_user_func_array([$this, 'resolveQueryBuilderFromRoot'], $arguments);

        // If there is no query builder returned, try to use the parent resolve method.
        if (!$query) {
            if (method_exists('parent', 'resolve')) {
                return call_user_func_array(['parent', 'resolve'], $arguments);
            } else {
                return null;
            }
        }

        $after = array_get($args, 'after');
        $before = array_get($args, 'before');
        $first = array_get($args, 'first');
        $last = array_get($args, 'last');
        $sort = array_get($args, 'sort');

        $countQuery = clone $query;
        if ($countQuery instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            $countQuery->getBaseQuery()->orders = null;
        } else if ($countQuery instanceof \Illuminate\Database\Eloquent\Builder) {
            $countQuery->getQuery()->orders = null;
        } else if( $countQuery instanceof \Illuminate\Database\Query\Builder) {
            $countQuery->orders = null;
        }
        $count = $countQuery->count();

        $offset = 0;
        $limit = 0;
        if ($first) {
            $limit = $first;
        }

        if ($last) {
            $limit = $last;
        }

        if ($after) {
            $offset = $after;
        }

        if ($before) {
            $offset = $before - $limit;
        }

        if ($sort) {
            foreach(explode(',', $sort) as $item) {
                $item = explode(' ', trim($item));
                $column = trim($item[0]);
                $direction = count($item) == 1 ? 'asc' : $item[1];
                $query->orderBy($column, $direction);
            }
        }

        $query->skip($offset)->take($limit);

        $hasNextPage = ($offset + $limit) < $count;
        $hasPreviousPage = $offset > 0;

        $resolveItemsArguments = array_merge([$query], $arguments);
        $items = call_user_func_array([$this, 'resolveItemsFromQueryBuilder'], $resolveItemsArguments);
        $collection = $this->getCollectionFromItems($items, $offset, $limit, $hasPreviousPage, $hasNextPage);

        return $collection;
    }
}
