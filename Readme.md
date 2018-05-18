# Laravel GraphQL

Use Facebook GraphQL with Laravel 5 or Lumen. It is based on the PHP implementation [here](https://github.com/webonyx/graphql-php). You can find more information about GraphQL in the [GraphQL Introduction](http://facebook.github.io/react/blog/2015/05/01/graphql-introduction.html) on the [React](http://facebook.github.io/react) blog or you can read the [GraphQL specifications](https://facebook.github.io/graphql/). This is a work in progress.

This package is compatible with Eloquent model (or any other data source). See the example below.

[![Latest Stable Version](https://poser.pugx.org/folklore/graphql/v/stable.svg)](https://packagist.org/packages/folklore/graphql)
[![Build Status](https://travis-ci.org/Folkloreatelier/laravel-graphql.png?branch=master)](https://travis-ci.org/Folkloreatelier/laravel-graphql)
[![Total Downloads](https://poser.pugx.org/folklore/graphql/downloads.svg)](https://packagist.org/packages/folklore/graphql)

----
### To use laravel-graphql with Relay, check the [feature/relay](https://github.com/Folkloreatelier/laravel-graphql/tree/feature/relay) branch.
----

## Installation

Version 1.0 is released. If you are upgrading from older version, you can check [Upgrade to 1.0](docs/upgrade.md).

#### Dependencies:

* [Laravel 5.x](https://github.com/laravel/laravel) or [Lumen](https://github.com/laravel/lumen)
* [GraphQL PHP](https://github.com/webonyx/graphql-php)


**1-** Require the package via Composer in your `composer.json`.
```json
{
  "require": {
    "folklore/graphql": "~1.0.0"
  }
}
```

**2-** Run Composer to install or update the new requirement.

```bash
$ composer install
```

or

```bash
$ composer update
```

### Laravel >= 5.5.x

**1-** Publish the configuration file

```bash
$ php artisan vendor:publish --provider="Folklore\GraphQL\ServiceProvider"
```

**2-** Review the configuration file

```
config/graphql.php
```

### Laravel <= 5.4.x

**1-** Add the service provider to your `config/app.php` file
```php
Folklore\GraphQL\ServiceProvider::class,
```

**2-** Add the facade to your `config/app.php` file
```php
'GraphQL' => Folklore\GraphQL\Support\Facades\GraphQL::class,
```

**3-** Publish the configuration file

```bash
$ php artisan vendor:publish --provider="Folklore\GraphQL\ServiceProvider"
```

**4-** Review the configuration file

```
config/graphql.php
```

### Lumen

**1-** Load the service provider in `bootstrap/app.php`
```php
$app->register(Folklore\GraphQL\LumenServiceProvider::class);
```

**2-** For using the facade you have to uncomment the line `$app->withFacades();` in `bootstrap/app.php`

After uncommenting this line you have the `GraphQL` facade enabled

```php
$app->withFacades();
```

**3-** Publish the configuration file

```bash
$ php artisan graphql:publish
```

**4-** Load configuration file in `bootstrap/app.php`

*Important*: this command needs to be executed before the registration of the service provider

```php
$app->configure('graphql');
...
$app->register(Folklore\GraphQL\LumenServiceProvider::class)
```

**5-** Review the configuration file

```
config/graphql.php
```

## Documentation

- [Upgrade to 1.0](docs/upgrade.md)

## Usage

- [Schemas](#schemas)
- [Creating a query](#creating-a-query)
- [Creating a mutation](#creating-a-mutation)
- [Input Validation](#validation)

#### Advanced Usage
- [Query variables](docs/advanced.md#query-variables)
- [Query nested resource](docs/advanced.md#query-nested-resource)
- [Enums](docs/advanced.md#enums)
- [Interfaces](docs/advanced.md#interfaces)
- [Custom field](docs/advanced.md#custom-field)
- [Eager loading relationships](docs/advanced.md#eager-loading-relationships)

### Schemas
Starting from version 1.0, you can define multiple schemas. Having multiple schemas can be useful if, for example, you want an endpoint that is public and another one that needs authentication.

You can define multiple schemas in the config:

```php
'schema' => 'default',

'schemas' => [
    'default' => [
        'query' => [
            //'users' => 'App\GraphQL\Query\UsersQuery'
        ],
        'mutation' => [
            //'updateUserEmail' => 'App\GraphQL\Query\UpdateUserEmailMutation'
        ]
    ],
    'secret' => [
        'query' => [
            //'users' => 'App\GraphQL\Query\UsersQuery'
        ],
        'mutation' => [
            //'updateUserEmail' => 'App\GraphQL\Query\UpdateUserEmailMutation'
        ]
    ]
]
```

Or you can add schema using the facade:

```php
GraphQL::addSchema('secret', [
    'query' => [
        'users' => 'App\GraphQL\Query\UsersQuery'
    ],
    'mutation' => [
        'updateUserEmail' => 'App\GraphQL\Query\UpdateUserEmailMutation'
    ]
]);
```

Afterwards, you can build the schema using the facade:

```php
// Will return the default schema defined by 'schema' in the config
$schema = GraphQL::schema();

// Will return the 'secret' schema
$schema = GraphQL::schema('secret');

// Will build a new schema
$schema = GraphQL::schema([
    'query' => [
        //'users' => 'App\GraphQL\Query\UsersQuery'
    ],
    'mutation' => [
        //'updateUserEmail' => 'App\GraphQL\Query\UpdateUserEmailMutation'
    ]
]);
```

Or you can request the endpoint for a specific schema

```
// Default schema
http://homestead.app/graphql?query=query+FetchUsers{users{id,email}}

// Secret schema
http://homestead.app/graphql/secret?query=query+FetchUsers{users{id,email}}
```

### Creating a query

First you need to create a type.

```php
namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class UserType extends GraphQLType
{
    protected $attributes = [
        'name' => 'User',
        'description' => 'A user'
    ];

    /*
    * Uncomment following line to make the type input object.
    * http://graphql.org/learn/schema/#input-types
    */
    // protected $inputObject = true;

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The id of the user'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'The email of user'
            ]
        ];
    }

    // If you want to resolve the field yourself, you can declare a method
    // with the following format resolve[FIELD_NAME]Field()
    protected function resolveEmailField($root, $args)
    {
        return strtolower($root->email);
    }
}
```

Add the type to the `config/graphql.php` configuration file

```php
'types' => [
    'User' => 'App\GraphQL\Type\UserType'
]
```

You could also add the type with the `GraphQL` Facade, in a service provider for example.

```php
GraphQL::addType('App\GraphQL\Type\UserType', 'User');
```

Then you need to define a query that returns this type (or a list). You can also specify arguments that you can use in the resolve method.
```php
namespace App\GraphQL\Query;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use App\User;

class UsersQuery extends Query
{
    protected $attributes = [
        'name' => 'users'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('User'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::string()],
            'email' => ['name' => 'email', 'type' => Type::string()]
        ];
    }

    public function resolve($root, $args)
    {
        if (isset($args['id'])) {
            return User::where('id' , $args['id'])->get();
        } else if(isset($args['email'])) {
            return User::where('email', $args['email'])->get();
        } else {
            return User::all();
        }
    }
}
```

Add the query to the `config/graphql.php` configuration file

```php
'schemas' => [
    'default' => [
        'query' => [
            'users' => 'App\GraphQL\Query\UsersQuery'
        ],
        // ...
    ]
]
```

And that's it. You should be able to query GraphQL with a request to the url `/graphql` (or anything you choose in your config). Try a GET request with the following `query` input

```
query FetchUsers {
  users {
    id
    email
  }
}
```

For example, if you use homestead:
```
http://homestead.app/graphql?query=query+FetchUsers{users{id,email}}
```

### Creating a mutation

A mutation is like any other query, it accepts arguments (which will be used to do the mutation) and return an object of a certain type.

For example a mutation to update the password of a user. First you need to define the Mutation.

```php
namespace App\GraphQL\Mutation;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation;
use App\User;

class UpdateUserPasswordMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateUserPassword'
    ];

    public function type()
    {
        return GraphQL::type('User');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::nonNull(Type::string())],
            'password' => ['name' => 'password', 'type' => Type::nonNull(Type::string())]
        ];
    }

    public function resolve($root, $args)
    {
        $user = User::find($args['id']);

        if (!$user) {
            return null;
        }

        $user->password = bcrypt($args['password']);
        $user->save();

        return $user;
    }
}
```

As you can see in the `resolve` method, you use the arguments to update your model and return it.

You then add the mutation to the `config/graphql.php` configuration file

```php
'schema' => [
    'default' => [
        'mutation' => [
            'updateUserPassword' => 'App\GraphQL\Mutation\UpdateUserPasswordMutation'
        ],
        // ...
    ]
]
```

You should then be able to use the following query on your endpoint to do the mutation.

```
mutation users {
  updateUserPassword(id: "1", password: "newpassword") {
    id
    email
  }
}
```

if you use homestead:
```
http://homestead.app/graphql?query=mutation+users{updateUserPassword(id: "1", password: "newpassword"){id,email}}
```

### Validation

It is possible to add additional validation rules to inputs, using the Laravel `Validator` to perform validation against the `args`.
Validation is mostly used for Mutations, but can also be applied to Queries that take arguments.

Be aware that GraphQL has native types to define a field as either a List or as NonNull. Use those wrapping
types instead of native Laravel validation via `array` or `required`. This way, those constraints are
reflected through the schema and are validated by the underlying GraphQL implementation.

#### Rule definition

##### Inline Array

The preferred way to add rules is to inline them with the arguments of Mutations or Queries.

```php
//...
class UpdateUserEmailMutation extends Mutation
{
    //...

    public function args()
        {
            return [
                'email' => [
                    'name' => 'email',
                    'type' => Type::string(),
                    'rules' => [
                        'email',
                        'exists:users,email'
                    ]
                ],
            ];
        }
}
```

##### Inline Closure

Rules may also be defined as closures. They are called before the resolve function of the field is called
and receive the same arguments.

````php
'phone' => [
    'name' => 'phone',
    'type' => Type::nonNull(Type::string()),
    'rules' => function ($root, $args, $context, \GraphQL\Type\Definition\ResolveInfo $resolveInfo){
        return [];
    }
],
````

##### Rule Overwrites

You can overwrite inline rules of fields or nested Input Objects by defining them like this:

````php
public function rules()
{
    return [
        'email' => ['email', 'min:10'],
        'nested.value' => ['alpha_num'],
    ];
}
````

Be aware that those rules are always applied, even if the argument is not given. You may want to prefix
them with `sometimes` if the rule is optional.

#### Required Arguments

GraphQL has a built-in way of defining arguments as required, simply wrap them in a `Type::nonNull()`.

````php
'id' => [
    'name' => 'id',
    'type' => Type::nonNull(Type::string()),
],
````

The presence of such arguments is checked before the arguments even reach the resolver, so there is
no need to validate them through an additional rule, so you will not ever need `required`.
Defining required arguments through the Non-Null type is preferable because it shows up in the schema definition. 

Because GraphQL arguments are optional by default, the validation rules for them will only be applied if they are present.
If you need more sophisticated validation of fields, using additional rules like `required_with` is fine.

#### Input Object Rules

You may use Input Objects as arguments like this:

````php
'name' => [
    'name' => 'name',
    'type' => GraphQL::type('NameInputObject')
],
````

Rules defined in the Input Object are automatically added to the validation, even if nested Input Objects are used.
The definition of those rules looks like this:

````php
<?php

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;

class NameInputObject extends BaseType
{
    protected $inputObject = true;

    protected $attributes = [
        'name' => 'NameInputObject'
    ];
    
    public function fields()
    {
        return [
            'first' => [
                'name' => 'first',
                'type' => Type::string(),
                'rules' => ['alpha']
            ],
            'last' => [
                'name' => 'last',
                'type' => Type::nonNull(Type::string()),
                'rules' => ['alpha']
            ],
        ];
    }
}
```` 

Now, the rules in here ensure that if a name is passed to base field, it must contain at least a
last name, and the first and last name can only contain alphabetic characters.

#### Array Validation

GraphQL allows arguments to be defined as lists by wrapping them in `Type::listOf()`.
In most cases it is desirable to apply validation rules to the underlying elements of the array.
If a type is wrapped as a list, the inline rules are automatically applied to the underlying
elements.

````php
'links' => [
    'name' => 'links',
    'type' => Type::listOf(Type::string()), 
    'rules' => ['url', 'distinct'],
],
````

If validation on the array itself is required, you can do so by defining those rules seperately:

````php
public function rules()
{
    return [
        'links' => ['max:10']
    ];
}
```` 

This ensures that `links` is an array of at most 10, distinct urls.

#### Response format

When you execute a field with arguments, it will return the validation errors.
Since the GraphQL specification defines a certain format for errors, the validation error messages
are added to the error object as an extra `validation` attribute.

To find the validation error, you should check for the error with a `message`
equals to `'validation'`, then the `validation` attribute will contain the normal
errors messages returned by the Laravel Validator.

```json
{
  "data": {
    "updateUserEmail": null
  },
  "errors": [
    {
      "message": "validation",
      "locations": [
        {
          "line": 1,
          "column": 20
        }
      ],
      "validation": {
        "email": [
          "The email is invalid."
        ]
      }
    }
  ]
}
```
