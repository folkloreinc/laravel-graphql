<?php


return [
    
    // The prefix for routes
    'prefix' => 'graphql',
    
    // The routes to make GraphQL request. Either a string that will apply
    // to both query and mutation or an array containing the key 'query' and/or
    // 'mutation' with the according Route
    //
    // Example:
    //
    // 'routes' => [
    //     'query' => '/query',
    //     'mutation' => '/mutation'
    // ]
    //
    'routes' => '/',
    
    // The schema for query and/or mutation. It expects an array to provide
    // both the 'query' ObjectType and the 'mutation' ObjectType. You can also
    // provide directly an object GraphQL\Schema
    //
    // Example:
    //
    // 'routes' => new Schema($queryType, $mutationType)
    //
    // or
    //
    // 'routes' => [
    //     'query' => $queryType,
    //     'mutation' => $mutationType
    // ]
    //
    'schema' => [
        'queries' => [
            
        ],
        'mutations' => [
            
        ]
    ],
    
    'types' => [
        
    ]
    
];
