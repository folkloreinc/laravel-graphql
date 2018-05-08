<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ExamplesCustomAuthorizeQuery extends ExamplesQuery
{
    protected $attributes = [
        'name' => 'Examples authorize query'
    ];

    public function authorize($root, $args)
    {
        return false;
    }

    protected function unauthorized()
    {
        return 'custom';
    }
}
