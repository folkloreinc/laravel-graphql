<?php

namespace Folklore\GraphQL\Console;

class TypeMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:graphql:type
                            {name : The name of the type}
                            {--input : Generate an input type}
                            {--interface : Generate an interface type}
                            {--enum : Generate an enum type}
                            {--union : Generate an union type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new GraphQL type class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Type';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('input')) {
            return __DIR__.'/stubs/input.stub';
        } elseif ($this->option('interface')) {
            return __DIR__.'/stubs/interface.stub';
        } elseif ($this->option('enum')) {
            return __DIR__.'/stubs/enum.stub';
        } elseif ($this->option('union')) {
            return __DIR__.'/stubs/union.stub';
        }
        return __DIR__.'/stubs/type.stub';
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
        $stub = $this->replaceType($stub, $name);

        return $stub;
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
            'DummyType',
            $matches[1],
            $stub
        );

        return $stub;
    }
}
