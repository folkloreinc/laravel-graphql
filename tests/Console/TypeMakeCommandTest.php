<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

/**
 * @coversDefaultClass \Folklore\GraphQL\Console\TypeMakeCommand
 */
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

    public function testInterfaceTypeMake()
    {
        $exitCode = Artisan::call('make:graphql:type', [
            'name' => 'ConsoleInterfaceType',
            '--interface' => true
        ]);

        $path = app_path('GraphQL/Type/ConsoleInterfaceType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleInterfaceType', 'BaseInterfaceType');
    }

    public function testEnumTypeMake()
    {
        $exitCode = Artisan::call('make:graphql:type', [
            'name' => 'ConsoleEnumType',
            '--enum' => true
        ]);

        $path = app_path('GraphQL/Type/ConsoleEnumType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleEnumType', 'BaseEnumType');
    }

    public function testUnionTypeMake()
    {
        $exitCode = Artisan::call('make:graphql:type', [
            'name' => 'ConsoleUnionType',
            '--union' => true
        ]);

        $path = app_path('GraphQL/Type/ConsoleUnionType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleUnionType', 'BaseUnionType');
    }
}
