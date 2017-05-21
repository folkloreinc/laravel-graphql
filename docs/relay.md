# GraphQL Relay

This documentation file won't go into details on how to use Relay, you can read about it here: https://facebook.github.io/relay/.

## Usage
1. [Installation](#1-installation)
2. [Create a node](#2-create-a-node)
3. [Global ID](#3-global-id)
4. [Connections](#4-connections)
5. [Mutations](#5-mutations)

### 1. Installation

**1-** Add the service provider and the facade to your `config/app.php` file

```php
<?php

return [
    
    // ...
    
    'providers' => [
        
        // ...
        
        'Folklore\GraphQL\Relay\ServiceProvider',
    ],
    
    'facades' => [
        
        // ...
        
        'Relay' => 'Folklore\GraphQL\Relay\Support\Facades\Relay',
    ]
];
```

**2-** Review the `relay` section of the `config/graphql.php` config file. Especially the `schemas` option. By default, a `node` query will be added to the default schema. You can specify another schema or add it manually like this:

```php
<?php

return [
    
    // ...
    
    'relay' => [
        // Disable the auto adding of the node query
        'schemas' => null,
        
        // ...
    ],
    
    'schemas' => [
    	'default' => [
    		'query' => [
                // Add the node query to any schema you want
    			'node' => \Folklore\GraphQL\Relay\NodeQuery::class
    		],
    		// ...
    	]
    ]
    
    // ...
];
```

Same thing for the two types relay need, they will be added automatically according to the config file but you can add it manually:

```php
<?php

return [
    
    // ...
    
    'types' => [
    	'Node' => \Folklore\GraphQL\Relay\NodeInterface::class,
        'PageInfo' => \Folklore\GraphQL\Relay\PageInfoType::class
    ]
    
    // ...
];
```

### 2. Create a node

First you need to create a relay node. A node is nothing else than a type implementing the NodeInterface, and thus having an `id` field.

You can use the artisan command, which will create a type extending the `Folklore\GraphQL\Relay\Support\NodeType` class.

```shell
php artisan make:relay:node UserNode
```

or you can add the `Folklore\GraphQL\Relay\Support\Traits\TypeIsNode` trait to any type you already created.

In case of a newly created node type, don't forget to add it to the `types` section of `config/graphql.php`.

### 3. Global Id

To work with Relay, nodes need to use globally unique id. To achieve that with records from a database which typically have their id starting from 1, this package use a combination of the type name and the id to generate base64 unique id.

```php
$globalId = base64_encode($type.':'.$id);
```

So for a User type with id 1, it will look like this:

```php
$globalId = base64_encode('User:1'); // id: VXNlcjox
```

This is done automatically by the `NodeType` or the `TypeIsNode` trait. It will look for an existing `id` field and replace it with a `Folklore\GraphQL\Relay\NodeIdField` which will use the output of the previous `id` field to generate the global id, from the type and the id.

This solves the problem of outputing the correct global id. We also need to enable the other way around, get an object from a global id.

This is also done automatically. The `node` query defined here `Folklore\GraphQL\Relay\NodeQuery` decode the global id it receives to the type name and the actual id. It will then look for a `resolveById` method on this type so you can return the correct object for the given id. It's in this method that you will do the query to get an Eloquent model (or any other method).

Here is an example with a User node:

```php
<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\NodeType as BaseNodeType;
use GraphQL;

use App\User;

class UserNodeType extends BaseNodeType
{
    protected $attributes = [
        'name' => 'User',
        'description' => 'An user relay node'
    ];

    protected function fields()
    {
        return [
            // The id field here, will be automatically wrapped in the NodeIdField
            // and then resolve to a global id
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id field',
                'resolve' => function($root)
                {
                    // The resolve method is not mandatory but for the sake of the example.
                    // Here we return the value of the id from our eloquent model. $root
                    // is a User model. We don't need to think about the global id
                    // it will be generated from this id and the type name
                    return $root->id;
                }
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'The email field'
            ]
        ];
    }
    
    // We get the eloquent model from the id
    public function resolveById($id)
    {
        return User::find($id);
    }
}

```

### 4. Connections

You can define connections really easily. First, generate a connection type with the following artisan command:

```shell
php artisan make:relay:connection PhotosConnection
```

Review the file `App\GraphQL\Type\PhotosConnection` and add the edge type.

```php
<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\ConnectionType as BasePayloadType;
use GraphQL;

class PhotosConnection extends BaseConnectionType
{
    protected $attributes = [
        'name' => 'PhotosConnection',
        'description' => 'A relay photos connection'
    ];

    protected function edgeType()
    {
        // Add the edge type here
        return GraphQL::type('Photo');
    }
}

```

If your node type `Photo` doesn't already exists, you can create it:

```shell
php artisan make:relay:node PhotoNode
```

Then add both the PhotosConnection and PhotoNode type to the `types` section of `config/graphql.php`.

Once this is done, you can define a connections field on the `User` type.

```php
<?php

class UserNodeType extends NodeType
{
    protected $attributes = [
        'name' => 'User',
        'description' => 'An user relay node'
    ];

    protected function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id field'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'The email field'
            ],
            
            // Here you define the connection field as you would normally do
            'photos' => [
                'type' => GraphQL::type('PhotosConnection'),
                'description' => 'The photos of the user'
            ]
        ];
    }
    
    public function resolveById($id)
    {
        return User::find($id);
    }
}

```

If you define the field like this, you will need to define the connection arguments (`first`, `last`, `before`, `after`, `sort`) yourself. To make this easier, there is some helper methods on the `Relay` facade.

```php
<?php

class UserNodeType extends NodeType
{
    // ...

    protected function fields()
    {
        return [
            // ...
            
            // The `connectionField` instantiate the \Folklore\GraphQL\Relay\Support\ConnectionField
            // class which includes the pagination arguments.
            'photos' => Relay::connectionField([
                'type' => GraphQL::type('PhotosConnection'),
                'description' => 'The photos of the user',
                'resolve' => function ($root, $args) {
                    // Here you can use the $args to return the items you want
                    // according to the connection pagination.
                }
            ]),
            
            // You can also declare a field directly with the edge type without
            // the need to create a connection type using the method `connectionFieldFromEdgeType`
            'photos' => Relay::connectionFieldFromEdgeType(GraphQL::type('Photo'), [
                'description' => 'The photos of the user',
                'resolve' => function ($root, $args) {
                    // Here you can use the $args to return the items you want
                    // according to the connection pagination.
                }
            ]),
            
            // And finally, if you are using eloquent, you can use
            // the `connectionFieldFromEdgeTypeAndQueryBuilder` method to create
            // a field from an edge type and returning a query builder for you edges.
            'photos' => Relay::connectionFieldFromEdgeTypeAndQueryBuilder(
                GraphQL::type('Photo'),
                function ($root, $args) {
                    return $user->photos();
                    // or
                    return Photo::query()->where('user_id', $root->id);
                },
                [
                    'description' => 'The photos of the user',
                    'args' => [
                        // set a default sort order
                        'sort' => [
                            'type: 'Type::string(),
                            'defaultValue' => 'date_created'
                        ]
                    ]
                ]
            )
        ];
    }
    
    // ...
}

```

All those helper methods, instantiate the `\Folklore\GraphQL\Relay\Support\ConnectionField` class. You can also extend this class and create your custom field.

```php
<?php

use Folklore\GraphQL\Relay\Support\ConnectionField;

class PhotosConnectionField extends ConnectionField
{
    // Here you can define additional arguments to filter you connection
    protected function args()
    {
        return [
            'size' => [
                'name' => 'size',
                'type' => Type::string()
            ]
        ];
    }
    
    protected function type()
    {
        return GraphQL::type('PhotosConnection');
    }
    
    // Resolve your connection here by using the arguments.
    public function resolve($root, $args)
    {
        
    }
    
    // or declare the `resolveQueryBuilder` method to return a Query Builder and
    // let the field resolve it automatically
    public function resolveQueryBuilder($root, $args)
    {
        //return $root->photos();
    }
}

```

Then you can use this field on your type:

```php
<?php

class UserNodeType extends NodeType
{
    // ...

    protected function fields()
    {
        return [
            // ...
            
            'photos' => \App\GraphQL\Field\PhotosConnectionField::class
        ];
    }
    
    // ...
}

```

After that, you should be able to query your user node like so, from the `node` query:

```graphql
query GetUserWithPhotos($id: ID!) {
    node (id: $id) {
        id
        
        ... on User {
            email
            photos {
                edges {
                    cursor
                    node {
                        width
                        height
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
        }
    }
}
```

### 5. Mutations

To create a mutation in relay, you need both a input type and a payload type. You can create both of these types by using artisan. So let's say we want to create a mutation to update the user email.

First we create an input type:

```shell
php artisan make:relay:input UpdateUserEmailInput
```

Then, a payload type for the response:

```shell
php artisan make:relay:payload UpdateUserEmailPayload
```

These types work normally except that they will automatically have the `clientMutationId` field added, so you don't need to think about it.

Here is the input type:

```php
<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\InputType as BaseInputType;
use GraphQL;

class UpdateUserEmailInput extends BaseInputType
{
    protected $attributes = [
        'name' => 'UpdateUserEmailInput',
        'description' => 'An input to update the user email'
    ];

    protected function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id field'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'The email field'
            ]
        ];
    }
}

```

And the payload:

```php
<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\PayloadType as BasePayloadType;
use GraphQL;

class UpdateUserEmailPayload extends BasePayloadType
{
    protected $attributes = [
        'name' => 'UpdateUserEmailPayload',
        'description' => 'The payload for updating user'
    ];

    protected function fields()
    {
        return [
            'user' => [
                'type' => GraphQL::type('User'),
                'description' => 'The user type
            ]
        ];
    }
}

```

Finally you can create your mutation

```shell
php artisan make:relay:mutation UpdateUserEmailMutation
```

```php
<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\Mutation as BaseMutation;
use GraphQL;

class UpdateUserEmailMutation extends BaseMutation
{
    protected $attributes = [
        'name' => 'UpdateUserEmailMutation',
        'description' => 'The mutation to update a user email'
    ];

    protected function inputType()
    {
        return GraphQL::type('UpdateUserEmailInput');
    }

    protected function type()
    {
        return GraphQL::type('UpdateUserEmailPayload');
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $user = User::find($args['input']['id']);
        $user->email = $args['input']['email'];
        $user->save();
        
        return [
            'user' => user
        ];
    }
}

```

Finally, add both of the `UpdateUserEmailInput` and `UpdateUserEmailPayload` type to the `types` params of your config file and the mutation to your schema.
