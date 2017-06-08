<?php
namespace Folklore\GraphQL\View;

use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

class GraphiQLComposer
{
    public function compose(View $view)
    {
        $view->graphqlPath = app()->bound('router') && app('router')->has('graphql.query') ?
            route('graphql.query') : url('/graphql');
        if ('' !== $schema = (string) Route::current()->parameter('graphql_schema')) {
            $view->graphqlPath .= '/'. $schema;
        }
    }
}
