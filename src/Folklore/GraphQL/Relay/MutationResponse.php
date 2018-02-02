<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;

class MutationResponse extends Fluent
{
    /**
     * @var mixed
     */
    protected $clientMutationId;
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
     * @return mixed
     */
    public function setNode($node)
    {
        $this->originalNode = $node;
        $this->attributes   = $node instanceof Arrayable ? $node->toArray() : (array) $node;
        return $this;
    }

    /**
     * @param $clientMutationId
     * @return mixed
     */
    public function setClientMutationId($clientMutationId)
    {
        $this->clientMutationId = $clientMutationId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientMutationId()
    {
        return $this->clientMutationId;
    }
}
