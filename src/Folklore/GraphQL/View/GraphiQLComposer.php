<?php
namespace Folklore\GraphQL\View;

use InvalidArgumentException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

class GraphiQLComposer
{
    public function compose(View $view)
    {
        try {
            $hasRoute = route('graphql.query');
        } catch (InvalidArgumentException $e) {
            $hasRoute = false;
        }

        $schema = $view->graphql_schema;

        if (! empty($schema)) {
            $view->graphqlPath = $hasRoute ? route('graphql.query', ['graphql_schema' => $schema]) : url('/graphql/' . $schema);
        } else {
            $view->graphqlPath = $hasRoute ? route('graphql.query') : url('/graphql');
        }
    }
}
