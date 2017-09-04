<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class QueryMakeCommandTest extends ConsoleTestCase
{
    public function testQueryMake()
    {
        $exitCode = Artisan::call('make:graphql:query', [
            'name' => 'ConsoleQuery'
        ]);

        $path = app_path('GraphQL/Query/ConsoleQuery.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Query\ConsoleQuery', 'BaseQuery');
    }
}
