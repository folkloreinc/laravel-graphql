<?php


return [

    // The name of the default schema used when no argument is provided
    // to GraphQL::schema() or when the routes are used without the {graphql_schema}
    // parameter.
    'schema' => 'default',

    // The list of schemas for you graphql server. It expects an array to provide
    // both the 'query' fields and the 'mutation' fields. You can also
    // provide directly an instance of GraphQL\Schema
    //
    // Example:
    //
    // 'schemas' => [
    //     'default' => [
    //         'query' => [
    //              'users' => \App\GraphQL\Query\UsersQuery::class
    //          ],
    //          'mutation' => [
    //              'updateUserName' => \App\GraphQL\Mutation\UpdateUserNameMutation::class
    //          ]
    //     ],
    // ]
    //
    // If you don't specify a key, the "name" attribute of your Query or Mutation
    // will be used.
    //
    // [
    //     'query' => [
    //          \App\GraphQL\Query\UsersQuery::class
    //      ],
    //      'mutation' => [
    //          \App\GraphQL\Mutation\UpdateUserNameMutation::class
    //      ]
    // ]
    //
    // You can also use directly \GraphQL\Schema
    //
    // 'schemas' => [
    //     'default' => new \GraphQL\Schema($config)
    // ]
    //
    'schemas' => [
        'default' => [
            'query' => [
                
            ],
            'mutation' => [

            ]
        ]
    ],

    // The types available in the application. You can then access it from the
    // facade like this: GraphQL::type('User')
    //
    // Example:
    //
    // 'types' => [
    //     'User' => App\GraphQL\Type\UserType:class
    // ]
    //
    // or whitout specifying a key (it will use the "name" attribute of your Type)
    //
    // 'types' => [
    //     App\GraphQL\Type\UserType::class
    // ]
    //
    'types' => [

    ],

    // The prefix for routes. You can remove it by setting it to null.
    'routes_prefix' => 'graphql',

    // The routes to make GraphQL request. By default, both query and mutation
    // are set to {graphql_schema?} so you can make requests to /graphql or
    // /graphql/name_of_the_schema
    //
    // You can define other routes, like this:
    //
    // 'routes' => [
    //     'query' => 'other_query/{graphql_schema?}',
    //     'mutation' => 'mutation/{graphql_schema?}'
    // ]
    //
    // Or disable routes by setting routes to null
    //
    // 'routes' => null,
    //
    'routes' => [
        'query' => '{graphql_schema?}',
        'mutation' => '{graphql_schema?}'
    ],

    // The controller to use in GraphQL request. It expect an array containing
    // the key 'query' and/or 'mutation' with the according Controller path
    //
    // Example:
    //
    // 'controllers' => [
    //     'query' => '\Folklore\GraphQL\GraphQLController@query',
    //     'mutation' => '\Folklore\GraphQL\GraphQLController@mutation'
    // ]
    //
    'controllers' => [
        'query' => '\Folklore\GraphQL\GraphQLController@query',
        'mutation' => '\Folklore\GraphQL\GraphQLController@query'
    ],
    
    // The name of the input that will contains variables when you query the endpoint.
    // Some library use "variables", you can change it here.
    'request_variables_name' => 'variables',

    // Any middleware for the graphql routes group
    'middleware' => [],

    // This callable will received every Error objects for each errors GraphQL catch.
    // The method should return an array representing the error.
    //
    // Typically:
    // [
    //     'message' => '',
    //     'locations' => []
    // ]
    //
    'error_formatter' => [\Folklore\GraphQL\GraphQL::class, 'formatError'],
    
    // Introspection configuration
    'introspection' => [
        
        // Contains the path to the introspection query
        // https://github.com/graphql/graphql-js/blob/master/src/utilities/introspectionQuery.js
        'query' => base_path('resources/graphql/introspection.txt'),
        
        // Used by the "make:graphql:schema" command as a default path for
        // saving generated schema.
        'schema_output' => base_path('resources/graphql/schema.json')
    ],
    
    // Relay configuration
    'relay' => [
        
        // Define the schemas on which you would like to use relay. It will
        // automatically add the node query defined below to those schemas.
        // The parameter can be a string, an array of names or "*" for all schemas.
        'schemas' => 'default',
        
        // The Query class used for the node query
        'query' => [
            'node' => \Folklore\GraphQL\Relay\NodeQuery::class
        ],
        
        // The Type classes used for the Node interface and the PageInfo
        'types' => [
            'Node' => \Folklore\GraphQL\Relay\NodeInterface::class,
            'PageInfo' => \Folklore\GraphQL\Relay\PageInfoType::class
        ]
    ],
    
    // Config for GraphiQL (https://github.com/graphql/graphiql).
    // To disable GraphiQL, set this to null.
    'graphiql' => [
        'routes' => '/graphiql',
        'middleware' => [],
        'view' => 'graphql::graphiql',
        'composer' => \Folklore\GraphQL\View\GraphiQLComposer::class
    ],

    // Options to limit the query complexity and depth. See the doc
    // @ https://github.com/webonyx/graphql-php#security
    // for details. Disabled by default.
    'security' => [
        'query_max_complexity' => null,
        'query_max_depth' => null
    ]
];
