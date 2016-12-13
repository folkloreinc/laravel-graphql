<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field as BaseField;
use GraphQL;

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
        return self::toGlobalId($this->idType, $id);
    }
    
    public static function toGlobalId($type, $id)
    {
        return base64_encode($type.':'.$id);
    }
    
    public static function fromGlobalId($globalId)
    {
        return explode(':', base64_decode($globalId));
    }
    
    public static function getIdFromGlobalId($globalId)
    {
        list($type, $id) = self::fromGlobalId($globalId);
        return $id;
    }
    
    public static function getTypeFromGlobalId()
    {
        list($type, $id) = self::fromGlobalId($globalId);
        return $type;
    }
}
