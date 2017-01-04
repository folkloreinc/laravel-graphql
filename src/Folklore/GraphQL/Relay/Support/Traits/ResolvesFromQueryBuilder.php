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
    
    protected function resolveQueryBuilderFromRoot()
    {
        $queryBuilderResolver = $this->getQueryBuilderResolver();
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
    
    protected function getCollectionFromItems($items, $hasPreviousPage = false, $hasNextPage = false)
    {
        $collection = new EdgesCollection($items);
        $collection->setHasNextPage($hasNextPage);
        $collection->setHasPreviousPage($hasPreviousPage);
        return $collection;
    }
    
    public function resolve($root, $args)
    {
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
        
        $queryCountBefore = clone $query;
        $queryCountAfter = clone $query;
        
        if (isset($args['after'])) {
            $afterId = Relay::getIdFromGlobalId($args['after']);
            $this->scopeAfter($query, $afterId);
            $this->scopeAfter($queryCountAfter, $afterId);
        }
        
        if (isset($args['before'])) {
            $beforeId = Relay::getIdFromGlobalId($args['before']);
            $this->scopeBefore($query, $beforeId);
            $this->scopeBefore($queryCountBefore, $beforeId);
        }
        
        $hasNextPage = false;
        $hasPreviousPage = false;
        if (isset($args['first'])) {
            $this->scopeFirst($query, $args['first']);
            $hasNextPage = $queryCountAfter->count() > $args['first'];
        }
        
        if (isset($args['last'])) {
            $this->scopeLast($query, $args['last']);
            $hasPreviousPage = $queryCountBefore->count() > $args['last'];
        }
        
        $items = call_user_func_array([$this, 'resolveItemsFromQueryBuilder'], [$query] + $arguments);
        $collection = $this->getCollectionFromItems($items, $hasNextPage, $hasPreviousPage);
        
        return $collection;
    }
}
