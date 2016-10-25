## Upgrade to 1.0

### Multiple schemas
The main difference between versions prior 1.0 and 1.0 is the use of multiple schemas. You will need to update your config to have the following structure:

```php

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
