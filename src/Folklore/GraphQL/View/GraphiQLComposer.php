<?php
namespace Folklore\GraphQL\View;

use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

class GraphiQLComposer
{
    public function compose(View $view)
    {
        $hasRoute = app()->bound('router') && app('router')->has('graphql.query');
        $schema = $view->schema;
        if (isset($schema) && !empty($schema)) {
            $view->graphqlPath = $hasRoute ? route('graphql.query', [$schema]) : url('/graphql/'.$schema);
        } else {
            $view->graphqlPath = $hasRoute ? route('graphql.query') : url('/graphql');
        }
    }
}
