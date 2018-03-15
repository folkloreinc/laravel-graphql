<?php

namespace Folklore\GraphQL\Relay\Support;

use GraphQL\Type\Definition\Type;

use Folklore\GraphQL\Support\InputType as BaseInputType;
use Folklore\GraphQL\Relay\Support\Traits\HasClientMutationIdField;

class InputType extends BaseInputType
{
    use HasClientMutationIdField;
}
