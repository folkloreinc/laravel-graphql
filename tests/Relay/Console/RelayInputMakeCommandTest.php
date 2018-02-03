<?php

use Folklore\GraphQl\Relay\Support\InputType;

class RelayInputMakeCommandTest extends RelayConsoleTestCase
{
    public function testInputMake()
    {
        $exitCode = Artisan::call('make:relay:input', [
            'name' => 'InputType'
        ]);

        $path = app_path('GraphQL/Type/InputType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\InputType', InputType::class);
    }
}
