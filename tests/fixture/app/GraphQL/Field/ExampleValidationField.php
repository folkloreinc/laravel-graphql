<?php

namespace App\GraphQL\Field;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Field;
use Folklore\GraphQL\Support\Traits\ShouldValidate;

class ExampleValidationField extends Field
{
    use ShouldValidate;
    
    protected $attributes = [
        'name' => 'example_validation'
    ];
    
    protected function type()
    {
        return Type::listOf(Type::string());
    }

    protected function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::id(),
                'rules' => ['required']
            ]
        ];
    }

    public function resolve($root, $args)
    {
        return ['test'];
    }
}
