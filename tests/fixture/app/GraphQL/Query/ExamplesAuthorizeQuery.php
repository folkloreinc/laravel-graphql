<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use Folklore\GraphQL\Support\Traits\ShouldAuthorize;
use GraphQL;

use App\Data;

class ExamplesAuthorizeQuery extends ExamplesQuery
{
    use ShouldAuthorize;

    protected $attributes = [
        'name' => 'examplesAuthorize'
    ];

    protected function authorize()
    {
        return false;
    }
}
