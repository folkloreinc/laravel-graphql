<?php

namespace Folklore\GraphQL\Relay\Console;

use Folklore\GraphQL\Console\GeneratorCommand;

class InputMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:relay:input {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new GraphQL Relay mutation input class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'InputType';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/input.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\GraphQL\Type';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $this->replaceType($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceType($stub, $name)
    {
        preg_match('/([^\\\]+)$/', $name, $matches);
        $stub = str_replace(
            'DummyInput',
            $matches[1],
            $stub
        );

        return $stub;
    }
}
