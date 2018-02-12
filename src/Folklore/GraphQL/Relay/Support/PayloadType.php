<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\Support\Traits\HasClientMutationIdField;
use Folklore\GraphQL\Support\Type as BaseType;

class PayloadType extends BaseType
{
    use HasClientMutationIdField;
}
