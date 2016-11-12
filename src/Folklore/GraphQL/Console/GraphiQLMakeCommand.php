<?php

namespace Folklore\GraphQL\Console;

use Illuminate\Console\Command;

class GraphiQLMakeCommand extends Command
{
    protected $signature = 'make:graphql:graphiql';
    protected $description = 'Create a graphiql template in your app.';

    public function fire()
    {
        copy(
            __DIR__.'/stubs/graphiql.stub',
            base_path('resources/views/graphiql.blade.php')
        );

        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__.'/stubs/graphiql_routes.stub'),
            FILE_APPEND
        );

        $this->info('GraphiQL scaffolding generated successfully.');
    }
}
