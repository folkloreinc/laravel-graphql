<?php

namespace Folklore\GraphQL\Relay;

use Folklore\GraphQL\Support\Field as BaseField;
use GraphQL\Type\Definition\Type;

class NodeIdField extends BaseField
{
    /**
     * @var mixed
     */
    protected $idResolver;
    /**
     * @var mixed
     */
    protected $idType;

    /**
     * @var array
     */
    protected $attributes = [
        'description' => 'A relay node id field',
    ];

    protected function type()
    {
        return Type::nonNull(Type::id());
    }

    /**
     * @param $idResolver
     * @return mixed
     */
    public function setIdResolver($idResolver)
    {
        $this->idResolver = $idResolver;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdResolver()
    {
        return $this->idResolver;
    }

    /**
     * @param $idType
     * @return mixed
     */
    public function setIdType($idType)
    {
        $this->idType = $idType;
        return $this;
    }

    /**
     * @return mixed
     */
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
