<?php

use Folklore\GraphQl\Relay\Support\NodeType;

class RelayNodeMakeCommandTest extends RelayConsoleTestCase
{
    public function testNodeMake()
    {
        $exitCode = Artisan::call('make:relay:node', [
            'name' => 'NodeType'
        ]);

        $path = app_path('GraphQL/Type/NodeType.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\NodeType', NodeType::class);
    }
}
