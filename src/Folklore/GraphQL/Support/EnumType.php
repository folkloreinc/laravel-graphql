<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\EnumType as EnumObjectType;

class EnumType extends Type
{
    protected function values()
    {
        return [];
    }

    public function getValues()
    {
        $values = array_get($this->attributes, 'values');
        return !is_null($values) ? $values : $this->values();
    }

    public function setValues($values)
    {
        $this->attributes['values'] = $values;
        return $this;
    }

    public function toArray()
    {
        $attributes = parent::toArray();

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
