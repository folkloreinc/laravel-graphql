<?php

namespace Folklore\GraphQL\Tests;

use GraphQL;
use Artisan;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

use Folklore\GraphQL\Console\QueryMakeCommand;
use Folklore\GraphQL\Console\TypeMakeCommand;

class ConsoleTest extends TestCase
{
    
    protected function getEnvironmentSetUp($app)
    {
        Artisan::registerCommand($app->make(QueryMakeCommand::class));
        Artisan::registerCommand($app->make(TypeMakeCommand::class));
        $app->setBasePath(__DIR__.'/fixture');
    }
    
    public function tearDown()
    {
        unlink(app_path('GraphQL/Query/TestQuery.php'));
        rmdir(app_path('GraphQL/Query'));
        rmdir(app_path('GraphQL'));
        parent::tearDown();
    }
    
    /**
     * Test
     *
     * @test
     */
    public function testMakeQuery()
    {
        Artisan::call('make:graphql_query', [
            'name' => 'TestQuery'
        ]);
        
        $queryPath = app_path('GraphQL/Query/TestQuery.php');
        $this->assertTrue(file_exists($queryPath));
    }
}
