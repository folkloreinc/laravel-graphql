<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field as BaseField;
use Relay as RelayFacade;

class NodeIdField extends BaseField
{
    protected $idResolver;
    protected $idType;
    
    protected $attributes = [
        'description' => 'A relay node id field'
    ];
    
    public function type()
    {
        return Type::nonNull(Type::id());
    }
    
    public function setIdResolver($idResolver)
    {
        $this->idResolver = $idResolver;
    }
    
    public function setIdType($idType)
    {
        $this->idType = $idType;
    }

    public function resolve()
    {
        $id = call_user_func_array($this->idResolver, func_get_args());
        return RelayFacade::toGlobalId($this->idType, $id);
    }
}
