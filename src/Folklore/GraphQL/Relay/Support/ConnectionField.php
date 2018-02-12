<?php

namespace Folklore\GraphQL\Relay\Support;

use Folklore\GraphQL\Relay\Support\Traits\FieldIsConnection;
use Folklore\GraphQL\Relay\Support\Traits\ResolvesFromQueryBuilder;
use Folklore\GraphQL\Support\Field as BaseField;
use GraphQL;

class ConnectionField extends BaseField
{
    use FieldIsConnection, ResolvesFromQueryBuilder;
}
