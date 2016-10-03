<?php

use Illuminate\Http\Request;
$app = app();
$app->group(array(
    'prefix' => config('graphql.prefix'),
    'middleware' => config('graphql.middleware', [])
), function() use($app)
{
    //Routes
    $routes = config('graphql.routes');
    $queryRoute = null;
    $mutationRoute = null;
    if(is_array($routes))
    {
        $queryRoute = array_get($routes, 'query', null);
        $mutationRoute = array_get($routes, 'mutation', null);
    }
    else
    {
        $queryRoute = $routes;
        $mutationRoute = $routes;
    }
    //Controllers
    $controllers = config('graphql.controllers', '\Folklore\GraphQL\GraphQLController@query');
    $queryController = null;
    $mutationController = null;
    if(is_array($controllers))
    {
        $queryController = array_get($controllers, 'query', null);
        $mutationController = array_get($controllers, 'mutation', null);
    }
    else
    {
        $queryController = $controllers;
        $mutationController = $controllers;
    }

    //Query
    if($queryRoute)
    {
        $app->get($queryRoute, array(
            'as' => 'graphql.query',
            'uses' => $queryController
        ));
    }

    if($mutationRoute)
    {
        //Mutation
        $app->post($mutationRoute, array(
            'as' => 'graphql.mutation',
            'uses' => $mutationController
        ));
    }
});
