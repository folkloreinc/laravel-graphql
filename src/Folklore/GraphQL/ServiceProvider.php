<?php namespace Folklore\GraphQL;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Get the active router.
     *
     * @return Router
     */
    protected function getRouter()
    {
        return $this->app['router'];
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPublishes();

        $this->bootSchemas();

        $this->bootTypes();

        $this->bootSecurity();

        $this->bootRouter();

        $this->bootViews();
    }

    /**
     * Bootstrap publishes
     *
     * @return void
     */
    protected function bootPublishes()
    {
        $configPath = __DIR__.'/../../config';
        $viewsPath = __DIR__.'/../../resources/views';
        $resourcesPath = __DIR__.'/../../resources/graphql';

        $this->mergeConfigFrom($configPath.'/config.php', 'graphql');

        $this->loadViewsFrom($viewsPath, 'graphql');

        $this->publishes([
            $configPath.'/config.php' => config_path('graphql.php'),
        ], 'config');

        $this->publishes([
            $viewsPath => base_path('resources/views/vendor/graphql'),
        ], 'views');

        $this->publishes([
            $resourcesPath => base_path('resources/graphql'),
        ], 'resources');
    }

    /**
     * Bootstrap router
     *
     * @return void
     */
    protected function bootRouter()
    {
        $router = $this->getRouter();
        $graphql = $this->app['graphql'];

        //Update the schema route pattern when schema is added
        $this->app['events']->listen(\Folklore\GraphQL\Events\SchemaAdded::class, function () use ($graphql, $router) {
            $router->pattern('graphql_schema', $graphql->routerSchemaPattern());
        });

        $router->pattern('graphql_schema', $graphql->routerSchemaPattern());

        // Define routes
        if ($this->app['config']->get('graphql.routes')) {
            include __DIR__.'/routes.php';
        }
    }

    /**
     * Bootstrap Views
     *
     * @return void
     */
    protected function bootViews()
    {
        $config = $this->app['config'];
        $graphiQL = $config->get('graphql.graphiql', true);
        if ($graphiQL) {
            $view = $config->get('graphql.graphiql.view', 'graphql::graphiql');
            $composer = $config->get('graphql.graphiql.composer', \Folklore\GraphQL\View\GraphiQLComposer::class);
            $this->app['view']->composer($view, $composer);
        }
    }

    /**
     * Add schemas to GraphQL
     *
     * @return void
     */
    protected function bootSchemas()
    {
        $this->app['graphql']->addSchemas($this->app['config']->get('graphql.schemas', []));
        $this->app['graphql']->setDefaultSchema($this->app['config']->get('graphql.schema', 'default'));
    }

    /**
     * Add types to GraphQL
     *
     * @return void
     */
    protected function bootTypes()
    {
        $configTypes = $this->app['config']->get('graphql.types', []);
        $this->app['graphql']->addTypes($configTypes);
    }

    /**
     * Set security options
     *
     * @return void
     */
    protected function bootSecurity()
    {
        $config = $this->app['config'];
        $maxQueryComplexity = $config->get('graphql.security.query_max_complexity');
        $maxQueryDepth = $config->get('graphql.security.query_max_depth');
        if ($maxQueryComplexity !== null) {
            $this->app['graphql']->setMaxQueryComplexity($maxQueryComplexity);
        }
        if ($maxQueryDepth !== null) {
            $this->app['graphql']->setMaxQueryDepth($maxQueryDepth);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGraphQL();

        $this->registerCommands();
    }

    /**
     * Register GraphQL facade
     *
     * @return void
     */
    public function registerGraphQL()
    {
        $this->app->singleton('graphql', function ($app) {
            $graphql = new GraphQL($app);
            return $graphql;
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
            'MakeSchema', 'MakeType', 'MakeQuery', 'MakeMutation', 'MakeField'
        ];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        $this->commands(
            'command.graphql.make.schema',
            'command.graphql.make.type',
            'command.graphql.make.query',
            'command.graphql.make.mutation',
            'command.graphql.make.field'
        );
    }

    /**
     * Register the "make:graphql:schema" migration command.
     *
     * @return void
     */
    public function registerMakeSchemaCommand()
    {
        $this->app->singleton('command.graphql.make.schema', function ($app) {
            return new \Folklore\GraphQL\Console\SchemaCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:type" migration command.
     *
     * @return void
     */
    public function registerMakeTypeCommand()
    {
        $this->app->singleton('command.graphql.make.type', function ($app) {
            return new \Folklore\GraphQL\Console\TypeMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:query" migration command.
     *
     * @return void
     */
    public function registerMakeQueryCommand()
    {
        $this->app->singleton('command.graphql.make.query', function ($app) {
            return new \Folklore\GraphQL\Console\QueryMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:mutation" migration command.
     *
     * @return void
     */
    public function registerMakeMutationCommand()
    {
        $this->app->singleton('command.graphql.make.mutation', function ($app) {
            return new \Folklore\GraphQL\Console\MutationMakeCommand($app['files']);
        });
    }

    /**
     * Register the "make:graphql:field" migration command.
     *
     * @return void
     */
    public function registerMakeFieldCommand()
    {
        $this->app->singleton('command.graphql.make.field', function ($app) {
            return new \Folklore\GraphQL\Console\FieldMakeCommand($app['files']);
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
            'graphql',
            'command.graphql.make.type',
            'command.graphql.make.query',
            'command.graphql.make.mutation',
            'command.graphql.make.field'
        ];
    }
}
