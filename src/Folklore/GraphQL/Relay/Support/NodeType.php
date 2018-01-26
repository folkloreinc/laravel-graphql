<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\Support\Traits\TypeIsNode;
use Folklore\GraphQL\Support\Type as BaseType;

abstract class NodeType extends BaseType implements NodeContract
{
    use TypeIsNode;
}
