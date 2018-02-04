<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use Folklore\GraphQL\Type\Definition\ResolveInfo;

class ExamplesAuthorizeQuery extends ExamplesQuery
{
    protected $attributes = [
        'name' => 'Examples authorize query'
    ];

    public function authorize($root, $args, $context, ResolveInfo $info)
    {
        return false;
    }
}
