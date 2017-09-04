# Upgrade guide

1. [Upgrade from 1.0 to 1.1](#upgrade-from-1-0-to-1-1)
2. [Upgrade to 1.0](#upgrade-to-1-0)

## Upgrade from 1.0 to 1.1

### Changes in config file

There is some changes in version 1.1, mainly in the config file. They should be backward compatible but since those changes are cosmetic, it could be a good thing to update it.

Changes in the `config/graphql.php`
```php
<?php

return [
    
    // prefix config name has been renamed to routes_prefix
    
    'prefix' => 'graphql'
    
    // become :
    
    'routes_prefix' => 'graphql',
    
    // ---------------------------------------
    
    // variables_input_name has been renamed to request_variables_name
    // and variables is the new default
    
    'variables_input_name' => 'params'
    
    // become :
    
    'request_variables_name' => 'variables',
    
];
```

### Introspection methods

There is two new methods on the `GraphQL` facade for introspection.

```php

// This method will run an introspection query on your default schema
$schema = GraphQL::introspection();

// You can specify anothe schema with the first argument
$schema = GraphQL::introspection('custom_schema');

```

You can also access the introspection query with the following method:

```php
$query = GraphQL::introspectionQuery();
```

The introspection query is loaded from `resources/graphql/introspectionQuery.txt`. You can change this by editing the new `introspection` section in the `config/graphql.php` file.

Also, you can generate a schema json file (to use with relay) with the following artisan command:

```shell
php artisan make:graphql:schema
```

By the default the schema will be outputed in the `resources/graphql/schema.json` file. You can change this by editing the new `introspection` section in the `config/graphql.php` file or by passing a `--path=` option to the command.

### Relay support



## Upgrade to 1.0

### Multiple schemas
The main difference between versions prior 1.0 and 1.0 is the use of multiple schemas. You will need to update your config to have the following structure:

```php
<?php

return [
    'schema' => 'default',
    
	'schemas' => [
		'default' => [
			'query' => [
				// Your queries
			],
			'mutation' => [
				// Your mutations
			]
		]
	]
    
    // ...
];	

```

### Routes
If you want to use routes that can accept schema name, you need to change `routes` to the following:

```php

'routes' => '{graphql_schema?}',

// or if you use different routes for query and mutation

'routes' => [
	'query' => 'query/{graphql_schema?}',
	'mutation' => 'mutation/{graphql_schema?}'
],

```

### Facade methods
The method `GraphQL::addQuery` and `GraphQL::addMutation` has been removed since it doesn't make sense with multiple schemas. You can use the new `GraphQL::addSchema` method to add new schemas.

### Context
Since graphql-php v0.7 the arguments passed to the `resolve` method has changed. There is a third argument called `context`.

```php
public function resolve($root, $args, $context, ResolveInfo $info)
{
	
}
```
