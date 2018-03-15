<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Support\Type as BaseType;
use Folklore\GraphQL\Relay\Support\Traits\HasClientMutationIdField;

class PayloadType extends BaseType
{
    use HasClientMutationIdField;
}
