<?php namespace Folklore\GraphQL\Relay;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootTypes();

        $this->bootSchemas();
    }

    /**
     * Add schemas from config
     *
     * @return void
     */
    protected function bootSchemas()
    {
        $query   = config('graphql.relay.query', []);
        $schemas = config('graphql.relay.schemas');
        if ($schemas === null) {
            return null;
        } elseif ($schemas === '*') {
            $schemas = array_keys(config('graphql.schemas', []));
        } else {
            $schemas = (array) $schemas;
        }

        $allSchemas = $this->app['graphql']->getSchemas();
        foreach ($allSchemas as $name => $schema) {
            if (!in_array($name, $schemas)) {
                continue;
            }
            $schema['query'] = array_merge($schema['query'], $query);
            $this->app['graphql']->addSchema($name, $schema);
        }
    }

    /**
     * Add types from config
     *
     * @return void
     */
    protected function bootTypes()
    {
        $types = config('graphql.relay.types');
        if (is_array($types)) {
            $this->app['graphql']->addTypes($types);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRelay();

        $this->registerCommands();
    }

    /**
     * Register Relay facade
     *
     * @return void
     */
    public function registerRelay()
    {
        $this->app->singleton('graphql.relay', function ($app) {
            $relay = new Relay($app);
            return $relay;
        });
    }

    /**
     * Register console commands
     *
     * @return void
     */
    public function registerCommands()
    {
        $commands = [
            'MakeNode', 'MakeMutation', 'MakeInput', 'MakePayload', 'MakeConnection',
        ];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        $this->commands(
            'command.relay.make.node',
            'command.relay.make.mutation',
            'command.relay.make.input',
            'command.relay.make.payload',
            'command.relay.make.connection'
        );
    }

    /**
     * Register the "make:graphql:node" migration command.
     *
     * @return void
     */
    public function registerMakeNodeCommand()
    {
        $this->app->singleton('command.relay.make.node', function ($app) {
            return new \Folklore\GraphQL\Relay\Console\NodeMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:mutation" migration command.
     *
     * @return void
     */
    public function registerMakeMutationCommand()
    {
        $this->app->singleton('command.relay.make.mutation', function ($app) {
            return new \Folklore\GraphQL\Relay\Console\MutationMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:input" migration command.
     *
     * @return void
     */
    public function registerMakeInputCommand()
    {
        $this->app->singleton('command.relay.make.input', function ($app) {
            return new \Folklore\GraphQL\Relay\Console\InputMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:payload" migration command.
     *
     * @return void
     */
    public function registerMakePayloadCommand()
    {
        $this->app->singleton('command.relay.make.payload', function ($app) {
            return new \Folklore\GraphQL\Relay\Console\PayloadMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:payload" migration command.
     *
     * @return void
     */
    public function registerMakeConnectionCommand()
    {
        $this->app->singleton('command.relay.make.connection', function ($app) {
            return new \Folklore\GraphQL\Relay\Console\ConnectionMakeCommand($app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'graphql.relay',
            'command.relay.make.node',
            'command.relay.make.input',
            'command.relay.make.payload',
            'command.relay.make.connection',
        ];
    }
}
