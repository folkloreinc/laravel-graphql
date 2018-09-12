<?php

use GraphQL\Type\Definition\InputObjectType;
use Mockery as m;

class TypeMakeCommandTest extends TestCase
{
    protected $command;

    protected $tmpPath = __DIR__.'/appTest/GraphQL/Type/';

    public function setUp()
    {
        parent::setUp();

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

        $this->assertTrue(true);
    }

    public function testItMakeAInputObjectTypeWithObjectOption()
    {
        $this->mockGetPath('TestObjectInputType');

        $this->registerCommand();

        $this->artisan('make:graphql:type', ['name' => 'TestObjectInputType', '-O' => true]);

        include $this->tmpPath.'TestObjectInputType.php';

        $inputType  =  new \AppTest\GraphQL\Type\TestObjectInputType();

        $this->assertInstanceOf(InputObjectType::class, $inputType->toType());
    }

    public function testItErrorsIfTypeAlradyExists()
    {
        $this->command->shouldReceive('error')->with('Type already exists!');

        $this->mockGetPath('TestExistsType');

        $this->registerCommand();

        $this->artisan('make:graphql:type', ['name'=>'TestExistsType']);
        $this->artisan('make:graphql:type', ['name'=>'TestExistsType']);

        $this->assertTrue(true);
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
