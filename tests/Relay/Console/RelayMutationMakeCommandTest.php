<?php

use Folklore\GraphQl\Relay\Support\Mutation;

class RelayMutationMakeCommandTest extends RelayConsoleTestCase
{
    public function testMutationMake()
    {
        $exitCode = Artisan::call('make:relay:mutation', [
            'name' => 'TestMutation'
        ]);

        $path = app_path('GraphQL/Mutation/TestMutation.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Mutation\TestMutation', Mutation::class);
    }
}
