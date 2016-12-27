<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;

class ConsoleTest extends TestCase
{
    protected $clearPaths = [
        //'resources/graphql/schema.json',
        'GraphQL/Type/ConsoleType.php',
        'GraphQL/Type/ConsoleInputType.php',
        'GraphQL/Query/ConsoleQuery.php',
        'GraphQL/Mutation/ConsoleMutation.php',
        'GraphQL/Field/ConsoleField.php',
        'resources/graphql/',
        'GraphQL/Type/',
        'GraphQL/Query/',
        'GraphQL/Mutation/',
        'GraphQL/Field/',
        'GraphQL/'
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
    
    public function testQueryMake()
    {
        $exitCode = Artisan::call('make:graphql:query', [
            'name' => 'ConsoleQuery'
        ]);
        
        $path = app_path('GraphQL/Query/ConsoleQuery.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Query\ConsoleQuery', 'BaseQuery');
    }
    
    public function testMutationMake()
    {
        $exitCode = Artisan::call('make:graphql:mutation', [
            'name' => 'ConsoleMutation'
        ]);
        
        $path = app_path('GraphQL/Mutation/ConsoleMutation.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Mutation\ConsoleMutation', 'BaseMutation');
    }
    
    public function testFieldMake()
    {
        $exitCode = Artisan::call('make:graphql:field', [
            'name' => 'ConsoleField'
        ]);
        
        $path = app_path('GraphQL/Field/ConsoleField.php');
        $this->assertGeneratorCommand($path, '\App\GraphQL\Field\ConsoleField', 'BaseField');
    }
    
    public function testSchemaMake()
    {
        $exitCode = Artisan::call('make:graphql:schema');
        
        $path = base_path('resources/graphql/schema.json');
        $this->assertTrue(file_exists($path));
        $contents = file_get_contents($path);
        $this->assertEquals(json_decode($contents, true), GraphQL::introspection());
    }
}
