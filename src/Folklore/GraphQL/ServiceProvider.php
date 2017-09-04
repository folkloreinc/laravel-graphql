<?php namespace Folklore\GraphQL;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
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
        $this->bootPublishes();

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
        if ($this->app['config']->get('graphql.routes')) {
            $router = $this->app['router'];
            include __DIR__.'/routes.php';
        }
    }

    /**
     * Boot views
     *
     * @return void
     */
    protected function bootViews()
    {
        $config = $this->app['config'];

        if ($config->get('graphql.graphiql', true)) {
            $view = $config->get('graphql.graphiql.view', 'graphql::graphiql');
            $this->app['view']->composer($view, View\GraphiQLComposer::class);
        }
    }

    /**
     * Bootstrap router
     *
     * @param GraphQL $graphql
     * @return void
     */
    protected function addTypes(GraphQL $graphql)
    {
        $types = $this->app['config']->get('graphql.types', []);

        foreach ($types as $name => $type) {
            $graphql->addType($type, is_numeric($name) ? null : $name);
        }
    }

    /**
     * Bootstrap Views
     *
     * @param GraphQL $graphql
     * @return void
     */
    protected function addSchemas(GraphQL $graphql)
    {
        $schemas = $this->app['config']->get('graphql.schemas', []);

        foreach ($schemas as $name => $schema) {
            $graphql->addSchema($name, $schema);
        }
    }

    /**
     * Bootstrap events
     *
     * @param GraphQL $graphql
     * @return void
     */
    protected function registerEventListeners(GraphQL $graphql)
    {
        // Update the schema route pattern when schema is added
        $this->app['events']->listen(Events\SchemaAdded::class, function () use ($graphql) {
            $schemaNames = array_keys($graphql->getSchemas());
            $this->app['router']->pattern('graphql_schema', '('.implode('|', $schemaNames).')');
        });

        $schemaNames = array_keys($graphql->getSchemas());
        $this->app['router']->pattern('graphql_schema', '('.implode('|', $schemaNames).')');
    }

    /**
     * Configure security from config
     *
     * @return void
     */
    protected function applySecurityRules(GraphQL $graphql)
    {
        $config = $this->app['config'];
        $maxQueryComplexity = $config->get('graphql.security.query_max_complexity');
        $maxQueryDepth = $config->get('graphql.security.query_max_depth');
        if ($maxQueryComplexity !== null) {
            $graphql->setMaxQueryComplexity($maxQueryComplexity);
        }
        if ($maxQueryDepth !== null) {
            $graphql->setMaxQueryDepth($maxQueryDepth);
        }

        $disableIntrospection = config('graphql.security.disable_introspection');
        if ($disableIntrospection === false) {
            $graphql->disableIntrospection();
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
    protected function registerGraphQL()
    {
        $this->app->singleton('graphql', function ($app) {

            $graphql = new GraphQL($app);

            $graphql->setDefaultSchema(config('graphql.schema'));

            $this->addTypes($graphql);

            $this->addSchemas($graphql);

            $this->registerEventListeners($graphql);

            $this->applySecurityRules($graphql);

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
