<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class MutationMakeCommandTest extends ConsoleTestCase
{
    public function testMutationMake()
    {
        $exitCode = Artisan::call('make:graphql:mutation', [
            'name' => 'ConsoleMutation'
        ]);

        $path = app_path('GraphQL/Mutation/ConsoleMutation.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Mutation\ConsoleMutation', 'BaseMutation');
    }
}
