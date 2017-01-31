<?php

namespace Folklore\GraphQL\View;

class GraphiQLComposer
{
    public function compose($view)
    {
        $view->graphqlPath = route('graphql.get.query');
    }
}
