<?php

use Illuminate\Http\Request;

$schemaParameterPattern = '/\{\s*graphql\_schema\s*\?\s*\}/';

$router->group(array(
    'prefix' => config('graphql.routes_prefix', 'graphql'),
    'middleware' => config('graphql.middleware', [])
), function ($router) {

    // Get routes from config. If routes is a string, it will apply to both query
    // and mutation.
    $routes = config('graphql.routes');
    $queryRoute = array_get($routes, 'query', is_string($routes) ? $routes:null);
    $mutationRoute = array_get($routes, 'mutation', is_string($routes) ? $routes:null);

    // Get controllers from config. If controllers is a string, it will apply to
    // both query and mutation.
    $controllers = config('graphql.controllers', '\Folklore\GraphQL\GraphQLController@query');
    $queryController = array_get($controllers, 'query', is_string($controllers) ? $controllers:null);
    $mutationController = array_get($controllers, 'mutation', is_string($controllers) ? $controllers:null);

    $schemaParameterPattern = '/\{\s*graphql\_schema\s*\?\s*\}/';

    //Query
    if ($queryRoute) {
        $queryMethods = ['get', 'post'];
        // Remove optional parameter in Lumen. Instead, creates two routes.
        if (!$router instanceof \Illuminate\Routing\Router &&
            preg_match($schemaParameterPattern, $queryRoute)
        ) {
            foreach ($queryMethods as $method) {
                $router->$method(preg_replace($schemaParameterPattern, '', $queryRoute), array(
                    'as' => 'graphql.query.'.$method,
                    'uses' => $queryController
                ));
                $router->$method(preg_replace($schemaParameterPattern, '{graphql_schema}', $queryRoute), array(
                    'as' => 'graphql.query.'.$method.'.with_schema',
                    'uses' => $queryController
                ));
            }
            // default route
            $router->get(preg_replace($schemaParameterPattern, '', $queryRoute), array(
                'as' => 'graphql.query',
                'uses' => $queryController
            ));
        } else {
            $router->match($queryMethods, $queryRoute, array(
                'as' => 'graphql.query',
                'uses' => $queryController
            ));
            $router->post($queryRoute, array(
                'as' => 'graphql.query.post',
                'uses' => $queryController
            ));
        }
    }

    //Mutation routes (define only if different than query)
    if ($mutationRoute && $mutationRoute !== $queryRoute) {
        // Remove optional parameter in Lumen. Instead, creates two routes.
        if (!$router instanceof \Illuminate\Routing\Router &&
            preg_match($schemaParameterPattern, $mutationRoute)
        ) {
            $router->post(preg_replace($schemaParameterPattern, '', $mutationRoute), array(
                'as' => 'graphql.mutation',
                'uses' => $mutationController
            ));
            $router->post(preg_replace($schemaParameterPattern, '{graphql_schema}', $mutationRoute), array(
                'as' => 'graphql.mutation.with_schema',
                'uses' => $mutationController
            ));
            $router->get(preg_replace($schemaParameterPattern, '', $mutationRoute), array(
                'as' => 'graphql.mutation.get',
                'uses' => $mutationController
            ));
            $router->get(preg_replace($schemaParameterPattern, '{graphql_schema}', $mutationRoute), array(
                'as' => 'graphql.mutation.get.with_schema',
                'uses' => $mutationController
            ));
        } else {
            $router->post($mutationRoute, array(
                'as' => 'graphql.mutation',
                'uses' => $mutationController
            ));
            $router->get($mutationRoute, array(
                'as' => 'graphql.mutation.get',
                'uses' => $mutationController
            ));
        }
    }
});

//GraphiQL
$graphiQL = config('graphql.graphiql', true);
if ($graphiQL) {
    $graphiQLRoute = config('graphql.graphiql.routes', 'graphiql');
    $graphiQLController = config('graphql.graphiql.controller', '\Folklore\GraphQL\GraphQLController@graphiql');
    if (!$router instanceof \Illuminate\Routing\Router &&
        preg_match($schemaParameterPattern, $graphiQLRoute)
    ) {
        $router->get(preg_replace($schemaParameterPattern, '', $graphiQLRoute), [
            'as' => 'graphql.graphiql',
            'middleware' => config('graphql.graphiql.middleware', []),
            'uses' => $graphiQLController
        ]);
        $router->get(preg_replace($schemaParameterPattern, '{graphql_schema}', $graphiQLRoute), [
            'as' => 'graphql.graphiql.with_schema',
            'middleware' => config('graphql.graphiql.middleware', []),
            'uses' => $graphiQLController
        ]);
    } else {
        $router->get($graphiQLRoute, [
            'as' => 'graphql.graphiql',
            'middleware' => config('graphql.graphiql.middleware', []),
            'uses' => $graphiQLController
        ]);
    }
}
