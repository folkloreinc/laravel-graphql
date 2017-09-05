<?php

namespace Folklore\GraphQL\Support;

use GraphQL\Type\Definition\InputObjectType;

class InputType extends Type
{
    public function toType()
    {
        return new InputObjectType($this->toArray());
    }
}
