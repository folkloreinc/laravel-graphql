<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\UnionType as UnionObjectType;

class UnionType extends InterfaceType
{
    public function types()
    {
        return [];
    }

    public function getTypes()
    {
        $attributesTypes = array_get($this->attributes, 'types', []);
        return sizeof($attributesTypes) ? $attributesTypes : $this->types();
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

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
