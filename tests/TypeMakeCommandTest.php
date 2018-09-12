<?php

use Mockery as m;

class TypeMakeCommandTest extends TestCase
{
    protected $command;

    protected $tmpPath;

    public function setUp()
    {
        parent::setUp();

        $this->app->singleton('Illuminate\Contracts\Console\Kernel', TestKernel::class);

        $this->tmpPath = __DIR__.'/appTest/GraphQL/Type/';

        $this->command = m::mock(
            'Folklore\GraphQL\Console\TypeMakeCommand[error,getPath,rootNamespace]',
            [new \Illuminate\Filesystem\Filesystem()]
        )->shouldAllowMockingProtectedMethods();

        $this->command->shouldReceive('rootNamespace')->andReturn('AppTest');
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();

        exec('rm -rf '.__DIR__.'/appTest');
    }

    public function testItMakeANormalType()
    {
        $this->mockGetPath('TestNormalType');

        $this->registerCommand();

        $this->artisan('make:graphql:type', ['name'=>'TestNormalType']);

        $this->assertFileExists($this->tmpPath.'TestNormalType.php');
    }

    public function testItMakeAInputObjectTypeWithObjectOption()
    {
        $this->mockGetPath('TestObjectInputType');

        $this->registerCommand();

        $this->artisan('make:graphql:type', ['name' => 'TestObjectInputType', '-O' => true]);

        $this->assertFileExists($this->tmpPath.'TestObjectInputType.php');
    }

    public function testItErrorsIfTypeAlradyExists()
    {
        $this->command->shouldReceive('error')->with('Type already exists!');

        $this->mockGetPath('TestExistsType');

        $this->registerCommand();

        $this->artisan('make:graphql:type', ['name'=>'TestExistsType']);
        $this->artisan('make:graphql:type', ['name'=>'TestExistsType']);

        $this->assertFileExists($this->tmpPath.'TestExistsType.php');
    }

    private function registerCommand()
    {
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($this->command);
    }

    private function mockGetPath($name)
    {
        $this->command->shouldReceive('getPath')->andReturn($this->tmpPath.$name.'.php');
    }
}

class TestKernel extends \Illuminate\Foundation\Console\Kernel
{
    public function registerCommand($command)
    {
        $this->getArtisan()->add($command);
    }
}
