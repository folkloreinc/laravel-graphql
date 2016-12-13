<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\ObjectType;
use Folklore\GraphQL\Support\Type as BaseType;

class EdgeObjectType extends ObjectType
{
    public function withEdgeType($type)
    {
        $this->_fields = null;
        $currentFields = array_get($this->config, 'fields');
        $fieldsResolver = function () use ($currentFields, $type) {
            $fields = $currentFields();
            array_set($fields, 'node.type', $type);
            return $fields;
        };
        array_set($this->config, 'fields', $fieldsResolver);
        
        return $this;
    }
}
