<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;

class InterfaceType extends Type
{
    protected $typeResolver = null;
    
    protected function getTypeResolver()
    {
        if ($this->typeResolver) {
            return $this->typeResolver;
        }
        
        if (!method_exists($this, 'resolveType')) {
            return null;
        }
        
        $resolver = array($this, 'resolveType');
        return function () use ($resolver) {
            $args = func_get_args();
            return call_user_func_array($resolver, $args);
        };
    }
    
    protected function setTypeResolver($typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }
    
    public function toArray()
    {
        $attributes = parent::toArray();
        $resolver = $this->getTypeResolver();
        if (isset($resolver)) {
            $attributes['resolveType'] = $resolver;
        }
        return $attributes;
    }
    
    public function toType()
    {
        return new BaseInterfaceType($this->toArray());
    }
}
