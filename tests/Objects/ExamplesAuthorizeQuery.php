<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ExamplesAuthorizeQuery extends ExamplesQuery
{
    protected $attributes = [
        'name' => 'Examples authorize query'
    ];

    public function authorize($root, $args)
    {
        return false;
    }
}
