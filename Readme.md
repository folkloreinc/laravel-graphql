# Laravel GraphQL

Use Facebook GraphQL with Laravel 5


## Installation

#### Dependencies:

* [Laravel 5.x](https://github.com/laravel/laravel)
* [GraphQL PHP](https://github.com/webonyx/graphql-php)


#### Installation:

**1-** Require the package via Composer in your `composer.json`.
```json
{
	"require": {
		"folklore/graphql": "0.1.*"
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

**5-** Publish the configuration file and public files

```bash
$ php artisan vendor:publish --provider="Folklore\GraphQL\GraphQLServiceProvider"
```

**6-** Review the configuration file

```
app/config/graphql.php
```

## Usage

First you need to create a schema.

```php
    
    use GraphQL\Schema;
    use GraphQL\Type\Definition\ObjectType;
    use GraphQL\Type\Definition\Type;
    
    use App\User;
    
    class SchemaBuilder {
        
        public static function build()
        {
            /**
             * User
             */
            $type = new ObjectType([
                'name' => 'User',
                'description' => 'A user',
                'fields' => [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'The id of the user.'
                    ],
                    'email' => [
                        'type' => Type::string(),
                        'description' => 'The email of the user.',
                        'resolve' => function ($user)
                        {
                            return strtolower($user->email);
                        }
                    ]
                ]
            ]);
            
            /**
             * Users
             */
            $usersType = Type::listOf($userType);
            
            /**
             * Query
             */
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'users' => [
                        'type' => $usersType,
                        'args' => [
                            'id' => ['name' => 'id', 'type' => Type::int()]
                        ],
                        'resolve' => function ($root, $args)
                        {
                            if(isset($args['id']))
                            {
                                return User::find($args['id']);
                            }
                            else
                            {
                                return User::all();
                            }
                        }
                    ]
                ]
            ]);
            
            return new Schema($queryType);
        }
        
    }

```

The add the schema to the `config/graphql.php` configuration file

```php
    
    'schema' => SchemaBuilder::build()

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
