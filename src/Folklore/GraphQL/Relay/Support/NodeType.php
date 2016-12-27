<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Support\Type as BaseType;
use Folklore\GraphQL\Relay\Support\Traits\TypeIsNode;

abstract class NodeType extends BaseType implements NodeContract
{
    use TypeIsNode;
}
