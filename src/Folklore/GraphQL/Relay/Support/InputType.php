<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\Support\Traits\HasClientMutationIdField;
use Folklore\GraphQL\Support\InputType as BaseInputType;

class InputType extends BaseInputType
{
    use HasClientMutationIdField;
}
