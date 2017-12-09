<?php

namespace Folklore\GraphQL\Support\Contracts;

use GraphQL\Type\Definition\Type;

interface TypeConvertible
{
    /**
     * @return Type
     */
    public function toType();
}
