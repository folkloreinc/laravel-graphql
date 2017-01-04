<?php

namespace Folklore\GraphQL\Relay\Support\Traits;

use GraphQL\Type\Definition\Type;

trait FieldIsConnection
{
    protected function connectionArgs()
    {
        return [
            'first' => [
                'name' => 'first',
                'type' => Type::int()
            ],
            'last' => [
                'name' => 'last',
                'type' => Type::int()
            ],
            'after' => [
                'name' => 'after',
                'type' => Type::id()
            ],
            'before' => [
                'name' => 'before',
                'type' => Type::id()
            ]
        ];
    }
    
    public function getArgs()
    {
        $args = parent::getArgs();
        
        $connectionArgs = $this->connectionArgs();
        
        return array_merge($connectionArgs, $args);
    }
}
