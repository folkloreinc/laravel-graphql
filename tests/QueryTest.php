<?php

use Folklore\Support\Field;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class QueryTest extends FieldTest
{
    protected function getFieldClass()
    {
        return \App\GraphQL\Query\ExamplesQuery::class;
    }
    
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        $app['config']->set('graphql.types', [
            'Example' => \App\GraphQL\Type\ExampleType::class
        ]);
    }
}
