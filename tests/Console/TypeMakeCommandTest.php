<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class TypeMakeCommandTest extends ConsoleTestCase
{
    public function testTypeMake()
    {
        $exitCode = Artisan::call('make:graphql:type', [
            'name' => 'ConsoleType'
        ]);

        $path = app_path('GraphQL/Type/ConsoleType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleType', 'BaseType');
    }

    public function testInputTypeMake()
    {
        $exitCode = Artisan::call('make:graphql:type', [
            'name' => 'ConsoleInputType',
            '--input' => true
        ]);

        $path = app_path('GraphQL/Type/ConsoleInputType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleInputType', 'BaseInputType');
    }
}
