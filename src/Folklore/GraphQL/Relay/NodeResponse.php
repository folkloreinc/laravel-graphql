<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;

class NodeResponse extends Fluent
{
    /**
     * @var mixed
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $originalNode;

    /**
     * @return mixed
     */
    public function getOriginalNode()
    {
        return $this->originalNode;
    }

    /**
     * @param $node
     */
    public function setNode($node)
    {
        $this->originalNode = $node;
        $this->attributes   = $node instanceof Arrayable ? $node->toArray() : (array) $node;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
