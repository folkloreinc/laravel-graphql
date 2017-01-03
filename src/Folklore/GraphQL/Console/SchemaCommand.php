<?php

namespace Folklore\GraphQL\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use GraphQL;

class SchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphql:schema
        {schema? : The name of the schema to output. Leave empty for default.} 
        {--path= : The path of the generated schema.json file} 
        {--output : Output the schema instead of generating a file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a graphql schema';
    
    /**
     * Filesystem instance for fs operations
     *
     * @var Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $schema = $this->argument('schema');
        if (empty($schema)) {
            $schema = config('graphql.schema');
        }
        
        $return = GraphQL::introspection($schema);
        $json = json_encode($return, JSON_PRETTY_PRINT);
        
        if ($this->option('output')) {
            echo $json;
            exit(0);
        }
        
        $defaultPath = config('graphql.introspection.schema_output', base_path('resources/graphql/schema.json'));
        $path = $this->option('path') ? $this->option('path'):$defaultPath;
        $dirname = dirname($path);
        if (!$this->files->isDirectory($dirname)) {
            $this->files->makeDirectory($dirname, 0775, true);
            $this->line('<info>Created:</info> Parent directory '.$dirname);
        }
        
        $this->files->put($path, $json);
        $this->line('<info>Created:</info> Schema at '.$path);
    }
}
