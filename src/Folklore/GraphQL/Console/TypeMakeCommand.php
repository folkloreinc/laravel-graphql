<?php

namespace Folklore\GraphQL\Console;

use Illuminate\Console\GeneratorCommand;

class TypeMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:graphql:type 
                            {name : The name of the type class}
                            {--O|object : Create a new input object type}';

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
        return __DIR__.'/stubs/type.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\GraphQL\Type';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
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
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceType($stub, $name)
    {
        preg_match('/([^\\\]+)$/', $name, $matches);

        $search = ['DummyType', 'DummyInputObject'];
        $replace = [$matches[1]];

        $this->addInputObjectAttribute($replace);

        return str_replace(
            $search,
            $replace,
            $stub
        );
    }

    /**
     * Add input object attribute to replace type.
     *
     * @param array $replace
     */
    protected function addInputObjectAttribute(array &$replace): void
    {
        if ($this->option('object')) {
            array_push($replace, 'protected $inputObject = true;');
        } else {
            array_push($replace, '');
        }
    }
}
