<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;

class ConsoleTest extends TestCase
{
    
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        $app->setBasePath(__DIR__.'/fixture');
    }
    
    public function tearDown()
    {
        if (file_exists(app_path('GraphQL/Query/TestQuery.php'))) {
            unlink(app_path('GraphQL/Query/TestQuery.php'));
        }
        if (file_exists(app_path('GraphQL/Type/TestType.php'))) {
            unlink(app_path('GraphQL/Type/TestType.php'));
        }
        if (file_exists(app_path('GraphQL/Mutation/TestMutation.php'))) {
            unlink(app_path('GraphQL/Mutation/TestMutation.php'));
        }
        
        if (file_exists(app_path('GraphQL/Type'))) {
            rmdir(app_path('GraphQL/Type'));
        }
        if (file_exists(app_path('GraphQL/Query'))) {
            rmdir(app_path('GraphQL/Query'));
        }
        if (file_exists(app_path('GraphQL/Mutation'))) {
            rmdir(app_path('GraphQL/Mutation'));
        }
        if (file_exists(app_path('GraphQL'))) {
            rmdir(app_path('GraphQL'));
        }
        
        parent::tearDown();
    }
    
    /**
     * Test make query
     *
     * @test
     */
    public function testMakeQuery()
    {
        Artisan::call('make:graphql:query', [
            'name' => 'TestQuery'
        ]);
        
        $path = app_path('GraphQL/Query/TestQuery.php');
        $this->assertTrue(file_exists($path));
        
        $content = file_get_contents($path);
        $this->assertRegExp('/class TestQuery extends Query/', $content);
        $this->assertRegExp('/'.preg_quote('\'name\' => \'TestQuery\'').'/', $content);
    }
    
    /**
     * Test make type
     *
     * @test
     */
    public function testMakeType()
    {
        Artisan::call('make:graphql:type', [
            'name' => 'TestType'
        ]);
        
        $path = app_path('GraphQL/Type/TestType.php');
        $this->assertTrue(file_exists($path));
        
        $content = file_get_contents($path);
        $this->assertRegExp('/class TestType extends Type/', $content);
        $this->assertRegExp('/'.preg_quote('\'name\' => \'TestType\'').'/', $content);
    }
    
    /**
     * Test make mutation
     *
     * @test
     */
    public function testMakeMutation()
    {
        Artisan::call('make:graphql:mutation', [
            'name' => 'TestMutation'
        ]);
        
        $path = app_path('GraphQL/Mutation/TestMutation.php');
        $this->assertTrue(file_exists($path));
        
        $content = file_get_contents($path);
        $this->assertRegExp('/class TestMutation extends Mutation/', $content);
        $this->assertRegExp('/'.preg_quote('\'name\' => \'TestMutation\'').'/', $content);
    }
}
