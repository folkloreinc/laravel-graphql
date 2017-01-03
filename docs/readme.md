# Documentation

## Usage

- [Schemas](#schemas)
- [Creating a type](#creating-a-type)
- [Creating a query](#creating-a-query)
- [Creating a mutation](#creating-a-mutation)
- [Adding validation to mutation](#adding-validation-to-mutation)
- [Use GraphQL with Relay](relay.md)

## Advanced Usage
- [Query variables](docs/advanced.md#query-variables)
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

### Creating a type

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

	protected function fields()
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

You could also add the type with the `GraphQL` Facade, in a service provider for example.

```php

GraphQL::addType('App\GraphQL\Type\UserType', 'user');

```

### Creating a query

Then you need to define a query that returns this type (or a list). You can also specify arguments that you can use in the resolve method.
```php

namespace App\GraphQL\Query;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use App\User;

class UsersQuery extends Query {

	protected $attributes = [
		'name' => 'users'
	];

	protected function type()
	{
		return Type::listOf(GraphQL::type('User'));
	}

	protected function args()
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

class UpdateUserPasswordMutation extends Mutation {

	protected $attributes = [
		'name' => 'updateUserPassword'
	];

	protected function type()
	{
		return GraphQL::type('User');
	}

	protected function args()
	{
		return [
			'id' => ['name' => 'id', 'type' => Type::nonNull(Type::string())],
			'password' => ['name' => 'password', 'type' => Type::nonNull(Type::string())]
		];
	}

	public function resolve($root, $args)
	{
		$user = User::find($args['id']);
		if(!$user)
		{
			return null;
		}

		$user->password = bcrypt($args['password']);
		$user->save();

		return $user;
	}

}

```

As you can see in the `resolve` method, you use the arguments to update your model and return it.

You then add the muation to the `config/graphql.php` configuration file

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

#### Adding validation to mutation

It is possible to add validation rules to mutation. It uses the laravel `Validator` to performs validation against the `args`.

When creating a mutation, you can add a method to define the validation rules that apply by doing the following:

```php

namespace App\GraphQL\Mutation;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Mutation;
use App\User;

class UpdateUserEmailMutation extends Mutation {

	protected $attributes = [
		'name' => 'UpdateUserEmail'
	];

	protected function type()
	{
		return GraphQL::type('user');
	}

	protected function args()
	{
		return [
			'id' => ['name' => 'id', 'type' => Type::string()],
			'email' => ['name' => 'password', 'type' => Type::string()]
		];
	}

	protected function rules($root, $args, $context)
	{
		return [
			'id' => ['required'],
			'email' => ['required', 'email']
		];
	}

	public function resolve($root, $args, $context, ResolveInfo $info)
	{
		$user = User::find($args['id']);
		if(!$user)
		{
			return null;
		}

		$user->email = $args['email'];
		$user->save();

		return $user;
	}

}

```

Alternatively you can define rules with each args

```php

class UpdateUserEmailMutation extends Mutation {

	//...

	protected function args()
	{
		return [
			'id' => [
				'name' => 'id',
				'type' => Type::string(),
				'rules' => ['required']
			],
            
            // You can also use a closure that will be called with the same arguments
            // as the resolve method.
			'email' => [
				'name' => 'password',
				'type' => Type::string(),
				'rules' => function ($root, $args, $context) {
                    return ['required', 'email'];
                }
			]
		];
	}

	//...

}

```

When you execute a mutation, it will returns the validation errors. Since GraphQL specifications define a certain format for errors, the validation errors messages are added to the error object as a extra `validation` attribute. To find the validation error, you should check for the error with a `message` equals to `'validation'`, then the `validation` attribute will contain the normal errors messages returned by the Laravel Validator.

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
