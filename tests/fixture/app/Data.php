<?php

namespace App;

use GraphQL\Error;
use Folklore\GraphQL\Error\ValidationError;

class Data
{
    public static function get()
    {
        return [
            [
                'id' => 1,
                'name' => 'Test 1'
            ],
            [
                'id' => 2,
                'name' => 'Test 2',
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Item 1'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Item 2'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Item 3'
                    ]
                ]
            ],
            [
                'id' => 3,
                'name' => 'Test 3'
            ]
        ];
    }
    
    public static function getById($id)
    {
        $items = self::get();
        return array_first($items, function ($item) use ($id) {
            return (string)$item['id'] === (string)$id;
        });
    }
}
