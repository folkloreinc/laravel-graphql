<?php

namespace Folklore\GraphQL\Support;

use Illuminate\Support\Fluent;

use GraphQL\Type\Definition\EnumType as EnumObjectType;
use Folklore\GraphQL\Support\Type as BaseType;

class EnumType extends BaseType
{
    public function toType()
    {
        return new EnumObjectType($this->toArray());
    }
}
