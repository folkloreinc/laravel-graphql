<?php

namespace Folklore\GraphQL\Relay;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field as BaseField;

class NodeIdField extends BaseField
{
    protected $idResolver;
    protected $idType;

    protected $attributes = [
        'description' => 'A relay node id field'
    ];

    protected function type()
    {
        return Type::nonNull(Type::id());
    }

    public function setIdResolver($idResolver)
    {
        $this->idResolver = $idResolver;
        return $this;
    }

    public function getIdResolver()
    {
        return $this->idResolver;
    }

    public function setIdType($idType)
    {
        $this->idType = $idType;
        return $this;
    }

    public function getIdType()
    {
        return $this->idType;
    }

    public function resolve()
    {
        $id = call_user_func_array($this->idResolver, func_get_args());
        return app('graphql.relay')->toGlobalId($this->idType, $id);
    }
}
