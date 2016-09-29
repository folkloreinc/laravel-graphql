<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use Closure;
use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use Folklore\GraphQL\Tests\Objects\ExamplesQuery;

class QueryTest extends FieldTest
{
    protected function getFieldClass()
    {
        return ExamplesQuery::class;
    }
    
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('graphql.types', [
            'Example' => \Folklore\GraphQL\Tests\Objects\ExampleType::class
        ]);
    }
}
