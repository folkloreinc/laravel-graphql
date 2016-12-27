<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class RelayConsoleTest extends RelayTestCase
{
    protected $clearPaths = [
        'resources/graphql/schema.json',
        'GraphQL/Type/ConsoleNode.php',
        'GraphQL/Type/ConsoleInput.php',
        'GraphQL/Type/ConsolePayload.php',
        'resources/graphql/',
        'GraphQL/Type/'
    ];
    
    public function tearDown()
    {
        $this->tearDownFiles();
        parent::tearDown();
    }
    
    public function tearDownFiles()
    {
        foreach ($this->clearPaths as $path) {
            $path = app_path($path);
            if (!file_exists($path)) {
                continue;
            }
            if (is_dir($path)) {
                if (count(scandir($path)) > 2) {
                    continue;
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
    
    public function assertGeneratorCommand($path, $name, $extends = 'Base[A-Z][a-z]+')
    {
        require($path);
        $reflection = new ReflectionClass($name);
        $shortName = $reflection->getShortName();
        $this->assertTrue(file_exists($path));
        $contents = file_get_contents($path);
        $this->assertRegExp('/class '.$shortName.' extends '.$extends.'/', $contents);
        $this->assertRegExp('/'.preg_quote('\'name\' => \''.$shortName.'\',').'/', $contents);
    }
    
    public function testNodeMake()
    {
        $exitCode = Artisan::call('make:relay:node', [
            'name' => 'ConsoleNode'
        ]);
        
        $path = app_path('GraphQL/Type/ConsoleNode.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleNode', 'BaseType');
    }
    
    public function testInputMake()
    {
        $exitCode = Artisan::call('make:relay:input', [
            'name' => 'ConsoleInput'
        ]);
        
        $path = app_path('GraphQL/Type/ConsoleInput.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleInput', 'BaseInputType');
    }
    
    public function testPayloadMake()
    {
        $exitCode = Artisan::call('make:relay:payload', [
            'name' => 'ConsolePayload'
        ]);
        
        $path = app_path('GraphQL/Type/ConsolePayload.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsolePayload', 'BasePayloadType');
    }
    
    public function testConnectionMake()
    {
        $exitCode = Artisan::call('make:relay:connection', [
            'name' => 'ConsoleConnection'
        ]);
        
        $path = app_path('GraphQL/Type/ConsoleConnection.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Type\ConsoleConnection', 'BaseConnectionType');
    }
    
    public function testMutationMake()
    {
        $exitCode = Artisan::call('make:relay:mutation', [
            'name' => 'ConsoleMutation'
        ]);
        
        $path = app_path('GraphQL/Type/ConsoleMutation.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Mutation\ConsoleMutation', 'BaseMutation');
    }
}
