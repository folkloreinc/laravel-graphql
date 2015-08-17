# Laravel GraphQL

Use Facebook GraphQL with Laravel 5. It is based on the PHP implementation [here](https://github.com/webonyx/graphql-php). You can find more information about GraphQL in the [GraphQL Introduction](http://facebook.github.io/react/blog/2015/05/01/graphql-introduction.html) on the [React](http://facebook.github.io/react) blog or you can read the [GraphQL specifications](https://facebook.github.io/graphql/). This is a work in progress.

This package is compatible with Eloquent model (or any other data source). See the example below.

## Installation

#### Dependencies:

* [Laravel 5.x](https://github.com/laravel/laravel)
* [GraphQL PHP](https://github.com/webonyx/graphql-php)


#### Installation:

**1-** Require the package via Composer in your `composer.json`.
```json
{
	"require": {
		"folklore/graphql": "0.2.*"
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

**3-** Add the service provider to your `app/config/app.php` file
```php
'Folklore\GraphQL\GraphQLServiceProvider',
```

**4-** Add the facade to your `app/config/app.php` file
```php
'Image' => 'Folklore\GraphQL\Support\Facades\GraphQL',
```

**5-** Publish the configuration file

```bash
$ php artisan vendor:publish --provider="Folklore\GraphQL\GraphQLServiceProvider"
```

**6-** Review the configuration file

```
config/graphql.php
```

## Usage

First you need to create a type.

```php

	namespace App\GraphQL\Type;
	
	use GraphQL\Type\Definition\Type;
	use Folklore\GraphQL\Support\Type as GraphQLType;
    
    class UserType extends GraphQLType {
        
        protected $attributes = [
	        'name' => 'Bubble',
	        'description' => 'A bubble'
	    ];
	    
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
		'user' => 'App\GraphQL\Type\UserType'
	]

```

Then you need to define a query
```php

	namespace App\GraphQL\Query;
	
	use GraphQL;
	use GraphQL\Type\Definition\Type;
    
    use App\User;
    
    class UsersQuery extends Query {
        
        protected $attributes = [
	        'name' => 'Users query'
	    ];
	    
	    public function type()
	    {
	        return Type::listOf(GraphQL::type('user'));
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
	        if(isset($args['id']))
			{
				return User::find($args['id']);
			}
			else if(isset($args['email']))
			{
				return User::where('email', $args['email'])->get();
			}
			else
			{
				return User::all();
			}
	    }
        
    }

```

Add the query to the `config/graphql.php` configuration file

```php
    
    'schema' => [
		'query' => [
			'users' => 'App\GraphQL\Query\UsersQuery'
		],
		// ...
	]

```

And thats it. You should be able to query GraphQL with a request to the url `/graphql` (or anything you choose in your config). Try a GET request with the following `query` input

```
    query FetchUsers {
        users {
            id
            email
        }
    }
```

eg. http://homestead.app/graphql?query=query+FetchUsers{users{id,email}}
