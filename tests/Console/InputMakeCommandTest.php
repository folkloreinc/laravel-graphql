<?php

use Folklore\GraphQL\Console\InputMakeCommand;
use Orchestra\Testbench\TestCase as BaseTestCase;

class InputMakeCommandTest extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Folklore\GraphQL\ServiceProvider::class,
        ];
    }

    /** @test */
    public function it_makes_an_input_file()
    {
        $this->artisan('make:graphql:input', [
            'name' => 'ExampleInput',
        ]);

        $this->assertFileExists(app_path('GraphQL/Inputs') . '/ExampleInput.php');
        $this->assertFileEquals(app_path('GraphQL/Inputs') . '/ExampleInput.php', __DIR__ . '/ExampleInput.php');
    }
}
