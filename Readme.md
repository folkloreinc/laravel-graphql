# Laravel GraphQL

Use Facebook GraphQL with Laravel 5 or Lumen. It is based on the PHP implementation [here](https://github.com/webonyx/graphql-php). You can find more information about GraphQL in the [GraphQL Introduction](http://facebook.github.io/react/blog/2015/05/01/graphql-introduction.html) on the [React](http://facebook.github.io/react) blog or you can read the [GraphQL specifications](https://facebook.github.io/graphql/). This is a work in progress.

This package is compatible with Eloquent model (or any other data source). See the example below.

[![Latest Stable Version](https://poser.pugx.org/folklore/graphql/v/stable.svg)](https://packagist.org/packages/folklore/graphql)
[![Build Status](https://travis-ci.org/Folkloreatelier/laravel-graphql.png?branch=master)](https://travis-ci.org/Folkloreatelier/laravel-graphql)
[![Total Downloads](https://poser.pugx.org/folklore/graphql/downloads.svg)](https://packagist.org/packages/folklore/graphql)

## Installation

Version 1.1 is released. If you are upgrading from older version, you can check the [upgrade guide](docs/upgrade.md).

#### Dependencies:

* [Laravel 5.x](https://github.com/laravel/laravel) or [Lumen](https://github.com/laravel/lumen)
* [GraphQL PHP](https://github.com/webonyx/graphql-php)


**1-** Require the package via Composer in your `composer.json`.
```json
{
	"require": {
		"folklore/graphql": "~1.1.0"
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

### Laravel 5.x

**1-** Add the service provider to your `app/config/app.php` file
```php
'Folklore\GraphQL\ServiceProvider',
```

**2-** Add the facade to your `app/config/app.php` file
```php
'GraphQL' => 'Folklore\GraphQL\Support\Facades\GraphQL',
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

## Simple Usage

**1-** First create a type:

```
php artisan make:graphql:type UserType
```

This command will create a Type Class `UserType.php` in the `app/GraphQL/Type` folder. You can review the file and add fields.

```php

class UserType extends BaseType
{
    
    //...
    
    public function fields()
    {
        return [
            'id' => [
                'type' => Type::id(),
                'description' => 'The user id'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'The user email'
            ]
        ]
    }
    
    //...
    
}

```

Then you need to add the Type to the config in `config/graphql.php`, like so:

```php
<?php

return [
    
    //...
    
    'types' => [
        \App\GraphQL\Type\UserType::class
    ]
    
    //...
    
];
```

Or use the facade in a service provider, like this:

```php
class AppServiceProvider extends ServiceProvider
{
    //...
    
    public function boot()
    {
        GraphQL::addType(\App\GraphQL\Type\UserType::class);
    } 
     
    //...  
}
```

**2-** Then create a query:

```
php artisan make:graphql:query UserQuery
```

Review the file and add arguments, the type returned by the query and fill the resolve method

```php

class UserType extends BaseType
{
    
    //...
    
    public function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::id()),
                'description' => 'The user id'
            ]
        ]
    }
    
    public function type()
    {
        //This is the type we've created the step before
        return GraphQL::type('User');
    }
    
    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        //Take the arguments and get a user from an eloquent model
        $user = User::find($args['id']);
        
        return $user
    }
    
}

```

The you need to add the query to the default schema in the config `config/graphql.php`.

```php
<?php

return [
    
    //...
    
    'schemas' => [
        'default' => [
            'query' => [
                'user' => \App\GraphQL\Query\UserQuery::class
            ]
            //...
        ]
    ]
    
    //...
    
];
```

or using the facade:

```php
class AppServiceProvider extends ServiceProvider
{
    //...
    
    public function boot()
    {
        GraphQL::addSchema('default', [
            'query' => [
                'user' => \App\GraphQL\Query\UserQuery::class
            ]
        ]);
    } 
     
    //...  
}
```

**3-** Query your schema

You can then query your schema by sending a GET request to `/graphql` with the following input:

query
```graphql
query GetUser($id: ID!)
{
    user (id: $id)
    {
        id
        email
    }
}
```

variables
```json
{
    "id": "1"
}
```

## Documentation

- [All documentation](docs/index.md)
- [Relay usage](docs/relay.md)
- [Advanced usage](docs/advanced.md)
- [Upgrade guide](docs/upgrade.md)
