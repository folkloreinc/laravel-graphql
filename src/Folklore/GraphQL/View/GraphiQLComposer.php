<?php

namespace Folklore\GraphQL\View;

class GraphiQLComposer
{
    public function compose($view)
    {
        $view->graphqlPath = app()->bound('router') && app('router')->has('graphql.query') ?
            route('graphql.query') : url('/graphql');
    }
}
