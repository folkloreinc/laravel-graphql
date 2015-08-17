<?php

use Illuminate\Http\Request;

Route::group(array(
    'prefix' => config('graphql.prefix')
), function()
{
    $routes = config('graphql.routes');
    $queryRoute = null;
    $mutationRoute = null;
    if(is_array($routes))
    {
        if(isset($routes['query']))
        {
            $queryRoute = $routes['query'];
        }
        if(isset($routes['mutation']))
        {
            $mutationRoute = $routes['mutation'];
        }
        
    }
    else
    {
        $queryRoute = $routes;
        $mutationRoute = $routes;
    }
    
    //Query
    if($queryRoute)
    {
        Route::get($queryRoute, array(
            'as' => 'graphql.query',
            function(Request $request)
            {
                $query = $request->get('query');
                $params = $request->get('params');
                return app('graphql')->query($query, is_string($params) ? json_decode($params, true):$params);
            }
        ));
        Route::post($queryRoute, array(
            'as' => 'graphql.query',
            function(Request $request)
            {
                $query = $request->get('query');
                $params = $request->get('params');
                return app('graphql')->query($query, is_string($params) ? json_decode($params, true):$params);
            }
        ));
    }
    
    if($mutationRoute)
    {
        //Mutation
        Route::post($mutationRoute, array(
            'as' => 'graphql.mutation',
            function(Request $request)
            {
                $query = $request->get('query');
                $params = $request->get('params');
                return app('graphql')->query($query, is_string($params) ? json_decode($params, true):$params);
            }
        ));
    }
});
