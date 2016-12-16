<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use GraphQL;
use Folklore\GraphQL\Relay\NodeIdField;

trait TypeIsNode
{
    public function getFieldsForObjectType()
    {
        $currentFields = parent::getFieldsForObjectType();
        
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
    
    public function relayInterfaces()
    {
        return [
            GraphQL::type('Node')
        ];
    }
    
    public function getInterfaces()
    {
        $interfaces = parent::getInterfaces();
        $relayInterfaces = $this->relayInterfaces();
        return array_merge($interfaces, $relayInterfaces);
    }
}
