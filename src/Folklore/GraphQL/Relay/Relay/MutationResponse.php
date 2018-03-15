<?php

namespace Folklore\GraphQL\Relay;

use Illuminate\Support\Fluent;
use Illuminate\Contracts\Support\Arrayable;

class MutationResponse extends Fluent
{
    protected $clientMutationId;
    protected $originalNode;

    public function getOriginalNode()
    {
        return $this->originalNode;
    }

    public function setNode($node)
    {
        $this->originalNode = $node;
        $this->attributes = $node instanceof Arrayable ? $node->toArray():(array)$node;
        return $this;
    }

    public function setClientMutationId($clientMutationId)
    {
        $this->clientMutationId = $clientMutationId;
        return $this;
    }

    public function getClientMutationId()
    {
        return $this->clientMutationId;
    }
}
