<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL;
use Folklore\GraphQL\Relay\NodeIdField;

trait NodeTrait
{
    public function getFieldsWithRelay()
    {
        $currentFields = $this->getFields();
        
        $idResolver = null;
        $originalResolver = array_get($currentFields, 'id.resolve');
        if ($originalResolver) {
            $idResolver = function ($root) use ($originalResolver) {
                $id = call_user_func_array($originalResolver, func_get_args());
                return $id;
            };
        } else {
            $idResolver = function ($root) {
                return array_get($root, 'id');
            };
        }
        
        $nodeIdField = new NodeIdField();
        $nodeIdField->setIdResolver($idResolver);
        $nodeIdField->setIdType($this->name);
        $currentFields['id'] = $nodeIdField->toArray();
        
        return $currentFields;
    }
    
    public function getInterfacesWithRelay()
    {
        $interfaces = $this->interfaces();
        return array_merge($interfaces, [
            GraphQL::type('Node')
        ]);
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $attributes['fields'] = function () {
            return $this->getFieldsWithRelay();
        };
        $attributes['interfaces'] = $this->getInterfacesWithRelay();
        
        return $attributes;
    }
}
