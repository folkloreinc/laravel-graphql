<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;

class InterfaceType extends Type
{
    public function getTypeResolver()
    {
        $resolver = array_get($this->attributes, 'resolveType');
        if ($resolver) {
            return $resolver;
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

    public function setTypeResolver($resolver)
    {
        $this->attributes['resolveType'] = $resolver;
        return $this;
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
