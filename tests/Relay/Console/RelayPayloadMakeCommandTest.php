<?php

use Folklore\GraphQl\Relay\Support\PayloadType;

class RelayPayloadMakeCommandTest extends RelayConsoleTestCase
{
    public function testPayloadMake()
    {
        $exitCode = Artisan::call('make:relay:payload', [
            'name' => 'PayloadType'
        ]);

        $path = app_path('GraphQL/Type/PayloadType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\PayloadType', PayloadType::class);
    }
}
