<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ExamplesAuthenticatedQuery extends ExamplesQuery
{
    protected $attributes = [
        'name' => 'Examples authenticate query'
    ];

    public function authenticated($root, $args, $context)
    {
        return false;
    }
}
