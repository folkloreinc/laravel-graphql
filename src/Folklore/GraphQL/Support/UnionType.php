<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\UnionType as UnionObjectType;

class UnionType extends InterfaceType
{
    protected function types()
    {
        return [];
    }

    public function getTypes()
    {
        $types = array_get($this->attributes, 'types');
        return $types ? $types:$this->types();
    }

    public function setTypes($types)
    {
        $this->attributes['types'] = $types;
        return $this;
    }


    public function toArray()
    {
        $attributes = parent::toArray();

        $types = $this->getTypes();
        if (isset($types)) {
            $attributes['types'] = $types;
        }

        return $attributes;
    }

    public function toType()
    {
        return new UnionObjectType($this->toArray());
    }
}
