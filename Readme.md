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
'GraphQL' => 'Folklore\GraphQL\Support\Facades\GraphQL',
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
			'name' => 'User',
			'description' => 'A user'
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

Then you need to define a query that returns this type (or a list). You can also specify arguments that you can use in the resolve method.
```php

	namespace App\GraphQL\Query;
	
	use GraphQL;
	use GraphQL\Type\Definition\Type;
	use Folklore\GraphQL\Support\Query;    
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
				return User::where('id' , $args['id'])->get();
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

## Advanced usage

### Custom field

You can also define a field as a class if you want to reuse it in multiple types.

```php

namespace App\GraphQL\Fields;
	
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field;

class PictureField extends Field {
        
        protected $attributes = [
		'description' => 'A picture'
	];
		
	public function args()
	{
		return [
			'width' => [
				'type' => Type::int(),
				'description' => 'The width of the picture'
			],
			'height' => [
				'type' => Type::int(),
				'description' => 'The height of the picture'
			]
		];
	}
	
	protected function resolve($root, $args)
	{
		$width = isset($args['width']) ? $args['width']:100;
		$height = isset($args['height']) ? $args['height']:100;
		return 'http://placehold.it/'.$width.'x'.$height;
	}
        
}

```

You can then use it in your type declaration

```php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class UserType extends GraphQLType {
        
        protected $attributes = [
		'name' => 'User',
		'description' => 'A user'
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
			],
			//Instead of passing an array, you pass a class path to your custom field
			'picture' => App\GraphQL\Fields\PictureField::class
		];
	}

}

```

### Eager loading relationships

The third argument passed to a query's resolve method is an instance of `GraphQL\Type\Definition\ResolveInfo` which you can use to retrieve keys from the request. The following is an example of using this information to eager load related Eloquent models.

```php
	namespace App\GraphQL\Query;
	
	use GraphQL;
	use GraphQL\Type\Definition\Type;
	use GraphQL\Type\Definition\ResolveInfo;
	use Folklore\GraphQL\Support\Query;
	
	use App\User;

	class UsersQuery extends Query
	{
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
        
		public function resolve($root, $args, ResolveInfo $info)
		{
			$fields = $info->getFieldSelection($depth = 3);
			
			$users = User::query();
			
			foreach ($fields as $field => $keys) {
				if ($field === 'profile') {
					$users->with('profile');
				}
				
				if ($field === 'posts') {
					$users->with('posts');
				}
			}
			
			return $users->get();
		}
	}
```
