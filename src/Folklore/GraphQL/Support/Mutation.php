<?php

namespace Folklore\GraphQL\Support;

use Validator;
use Folklore\GraphQL\Error\ValidationError;
use Folklore\GraphQL\Support\Traits\ShouldValidate;

class Mutation extends Field
{
    use ShouldValidate;
}
