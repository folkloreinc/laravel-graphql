<?php

use Folklore\GraphQl\Relay\Support\ConnectionType;

class RelayConnectionMakeCommandTest extends RelayConsoleTestCase
{
    public function testConnectionMake()
    {
        $exitCode = Artisan::call('make:relay:connection', [
            'name' => 'ConnectionType'
        ]);

        $path = app_path('GraphQL/Type/ConnectionType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConnectionType', ConnectionType::class);
    }
}
