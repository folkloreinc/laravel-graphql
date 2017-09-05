<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\EnumType as EnumObjectType;

class EnumType extends Type
{
    public function values()
    {
        return [];
    }

    public function getValues()
    {
        $values = $this->values();
        $attributesValues = array_get($this->attributes, 'values', []);
        return sizeof($attributesValues) ? $attributesValues : $values;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        $values = $this->getValues();
        if (isset($values)) {
            $attributes['values'] = $values;
        }

        return $attributes;
    }

    public function toType()
    {
        return new EnumObjectType($this->toArray());
    }
}
