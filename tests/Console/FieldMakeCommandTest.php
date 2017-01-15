<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class FieldMakeCommandTest extends ConsoleTestCase
{
    public function testFieldMake()
    {
        $exitCode = Artisan::call('make:graphql:field', [
            'name' => 'ConsoleField'
        ]);

        $path = app_path('GraphQL/Field/ConsoleField.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Field\ConsoleField', 'BaseField');
    }
}
