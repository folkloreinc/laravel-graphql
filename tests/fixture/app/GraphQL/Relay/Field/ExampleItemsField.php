<?php

namespace App\GraphQL\Relay\Field;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Relay\Support\ConnectionField;
use Relay;
use GraphQL;

class ExampleItemsField extends ConnectionField
{
    protected function type()
    {
        return GraphQL::type('ExampleItemsConnection');
    }

    public function resolve($root, $args)
    {
        $items = array_get($root, $this->name);
        
        $newItems = [];
        foreach ($items as $item) {
            $globalId = Relay::toGlobalId('ExampleItem', $item['id']);
            
            if (isset($args['after']) && $args['after'] === $globalId) {
                $newItems = [];
            }
            
            $newItems[] = $item;
            
            if (isset($args['before']) && $args['before'] === $globalId) {
                break;
            }
        }
        
        if (isset($args['first'])) {
            $newItems = array_slice($newItems, 0, $args['first']);
        }
        
        if (isset($args['last'])) {
            $newItems = array_slice($newItems, -$args['last']);
        }
        
        return $newItems;
    }
}
