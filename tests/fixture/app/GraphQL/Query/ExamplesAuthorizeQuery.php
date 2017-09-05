<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use GraphQL;

use App\Data;

class ExamplesAuthorizeQuery extends ExamplesQuery
{
    protected $attributes = [
        'name' => 'examplesAuthorize'
    ];

    protected function authorize()
    {
        return false;
    }
}
