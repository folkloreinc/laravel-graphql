# Advanced Usage

- [Query variables](#query-variables)
- [Query nested resource](#query-nested-resource)
- [Enums](#enums)
- [Interfaces](#interfaces)
- [Custom field](#custom-field)
- [Eager loading relationships](#eager-loading-relationships)

### Query Variables

GraphQL offer you the possibility to use variables in your query so you don't need to "hardcode" value. This is done like that:

```
query FetchUserByID($id: String) {
    user(id: $id) {
        id
        email
    }
}
```

When you query the GraphQL endpoint, you can pass a `variables` parameter.

```
http://homestead.app/graphql?query=query+FetchUserByID($id:String){user(id:$id){id,email}}&variables={"id":"1"}
```

### Query nested resource

If you want to query nested resource like that :

```
query FetchUser{
    user(id: 123456789) {
        id
        posts(id: 987654321) {
            id
        }
    }
}
```

you need to add post field and implement resolveField method in UserType:

```
public function fields()
{
    return [
        'id' => [
            'type'        => Type::nonNull(Type::string()),
            'description' => 'Id of user',
        ],
        'posts' => [
            'args' => [
                'id' => [
                    'type'        => Type::string(),
                    'description' => 'id of the post',
                ],
            ],
            'type'        => Type::listOf(GraphQL::type('Post')),
            'description' => 'post description',
        ],
    ];
}
    
public function resolvePostsField($root, $args)
{
    if (isset($args['id'])) {
        return  $root->posts->where('id', $args['id']);
    }

    return $root->posts;
}
```

### Enums

Enumeration types are a special kind of scalar that is restricted to a particular set of allowed values.
Read more about Enums [here](http://graphql.org/learn/schema/#enumeration-types)

First create an Enum as an extention of the GraphQLType class:
```php
<?php
// app/GraphQL/Enums/EpisodeEnum.php
namespace App\GraphQL\Enums;

use Folklore\GraphQL\Support\Type as GraphQLType;

class EpisodeEnum extends GraphQLType {
    protected $enumObject = true;

    protected $attributes = [
        'name' => 'Episode',
        'description' => 'The types of demographic elements',
        'values' => [
            'NEWHOPE' => 'NEWHOPE',
            'EMPIRE' => 'EMPIRE',
            'JEDI' => 'JEDI',
        ],
    ];
}

```
Register the Enum in the 'types' array of the graphql.php config file:

```php
// config/graphql.php
'types' => [TestEnum' => TestEnumType::class ];
```

Then use it like:
```php
<?php
// app/GraphQL/Type/TestType.php
class TestType extends GraphQLType {
   public function fields()
   {
        return [
            'type' => [
                'type' => GraphQL::type('TestEnum')
            ]
        ]
   }
}
```
### Interfaces

You can use interfaces to abstract a set of fields. Read more about interfaces [here](http://graphql.org/learn/schema/#interfaces).

An implementation of an interface:

```php
<?php
// app/GraphQL/Interfaces/CharacterInterface.php
namespace App\GraphQL\Interfaces;

use GraphQL;
use Folklore\GraphQL\Support\InterfaceType;
use GraphQL\Type\Definition\Type;

class CharacterInterface extends InterfaceType {
    protected $attributes = [
            'name' => 'Character',
            'description' => 'Character interface.',
        ];
    
        public function fields() {
            return [
                'id' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'The id of the character.'
                ],
                'appearsIn' => [
                    'type' => Type::nonNull(Type::listOf(GraphQL::type('Episode'))),
                    'description' => 'A list of episodes in which the character has an appearance.'
                ],
            ];
        }
    
        public function resolveType($root) {
            // Use the resolveType to resolve the Type which is implemented trough this interface
            $type = $root['type'];
            if ($type === 'human') {
                return GraphQL::type('Human');
            } else if  ($type === 'droid') {
                return GraphQL::type('Droid');
            }
        }
}
```

A Type that implements an interface:

```php
<?php
// app/GraphQL/Types/HumanType.php
namespace App\GraphQL\Types;

use GraphQL;
use Folklore\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class HumanType extends GraphQLType {

    protected $attributes = [
        'name' => 'Human',
        'description' => 'A human.'
    ];

    public function fields() {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The id of the human.',
            ],
            'appearsIn' => [
                'type' => Type::nonNull(Type::listOf(GraphQL::type('Episode'))),
                'description' => 'A list of episodes in which the human has an appearance.'
            ],
            'totalCredits' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The total amount of credits this human owns.'
            ]
        ];
    }

    public function interfaces() {
        return [
            GraphQL::type('Character')
        ];
    }
}

```


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

    public function type(){
        return Type::string();
    }

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

use App\GraphQL\Fields\PictureField;

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
            'picture' => PictureField::class
        ];
    }

}

```

### Eager loading relationships

The third argument passed to a query's resolve method is an instance of `GraphQL\Type\Definition\ResolveInfo` which you can use to retrieve keys from the request. The following is an example of using this information to eager load related Eloquent models.

Your Query would look like

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

    public function resolve($root, $args, $context, ResolveInfo $info)
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

Your Type for User would look like

```php
<?php

namespace App\GraphQL\Type;

use Folklore\GraphQL\Support\Facades\GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class UserType extends GraphQLType
{
    /**
     * @var array
     */
    protected $attributes = [
        'name' => 'User',
        'description' => 'A user',
    ];

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'uuid' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The uuid of the user'
            ],
            'email' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The email of user'
            ],
            'profile' => [
                'type' => GraphQL::type('Profile'),
                'description' => 'The user profile',
            ],
            'posts' => [
                'type' => Type::listOf(GraphQL::type('Post')),
                'description' => 'The user posts',
            ]
        ];
    }
}

```

At this point we have a profile and a post type as expected for any model

```php
class ProfileType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Profile',
        'description' => 'A user profile',
    ];

    public function fields()
    {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => 'The name of user'
            ]
        ];
    }
}
```

```php
class PostType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Post',
        'description' => 'A post',
    ];

    public function fields()
    {
        return [
            'title' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The title of the post'
            ],
            'body' => [
                'type' => Type::string(),
                'description' => 'The body the post'
            ]
        ];
    }
}
```


Lastly your query would look like, if using Homestead

For example, if you use homestead:

```
http://homestead.app/graphql?query=query+FetchUsers{users{uuid, email, team{name}}}
```