<?php namespace Folklore\GraphQL;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
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
        $this->bootEvents();

        $this->bootPublishes();

        $this->bootTypes();

        $this->bootSchemas();

        $this->bootRouter();

        $this->bootViews();
        
        $this->bootSecurity();
    }

    /**
     * Bootstrap router
     *
     * @return void
     */
    protected function bootRouter()
    {
        if (config('graphql.routes')) {
            $router = $this->getRouter();
            include __DIR__.'/routes.php';
        }
    }

    /**
     * Bootstrap events
     *
     * @return void
     */
    protected function bootEvents()
    {
        //Update the schema route pattern when schema is added
        $this->app['events']->listen(\Folklore\GraphQL\Events\SchemaAdded::class, function () {
            $schemaNames = array_keys($this->app['graphql']->getSchemas());
            $this->getRouter()->pattern('graphql_schema', '('.implode('|', $schemaNames).')');
        });
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

        $this->mergeConfigFrom($configPath.'/config.php', 'graphql');
        
        $this->loadViewsFrom($viewsPath, 'graphql');

        $this->publishes([
            $configPath.'/config.php' => config_path('graphql.php'),
        ], 'config');

        $this->publishes([
            $viewsPath => base_path('resources/views/vendor/graphql'),
        ], 'views');
    }

    /**
     * Add types from config
     *
     * @return void
     */
    protected function bootTypes()
    {
        $configTypes = config('graphql.types');
        foreach ($configTypes as $name => $type) {
            if (is_numeric($name)) {
                $this->app['graphql']->addType($type);
            } else {
                $this->app['graphql']->addType($type, $name);
            }
        }
    }

    /**
     * Add schemas from config
     *
     * @return void
     */
    protected function bootSchemas()
    {
        $configSchemas = config('graphql.schemas');
        foreach ($configSchemas as $name => $schema) {
            $this->app['graphql']->addSchema($name, $schema);
        }
    }

    /**
     * Bootstrap Views
     *
     * @return void
     */
    protected function bootViews()
    {
        $graphiQL = config('graphql.graphiql', true);
        if ($graphiQL) {
            $view = config('graphql.graphiql.view', 'graphql::graphiql');
            app('view')->composer($view, \Folklore\GraphQL\View\GraphiQLComposer::class);
        }
    }
    
    /**
     * Configure security from config
     * @return void
     */
    protected function bootSecurity()
    {
        $maxQueryComplexity = config('graphql.security.query_max_complexity');
        if ($maxQueryComplexity !== null) {
            $queryComplexity = DocumentValidator::getRule('QueryComplexity');
            $queryComplexity->setMaxQueryComplexity($maxQueryComplexity);
        }

        $maxQueryDepth = config('graphql.security.query_max_depth');
        if ($maxQueryDepth !== null) {
            $queryDepth = DocumentValidator::getRule('QueryDepth');
            $queryDepth->setMaxQueryDepth($maxQueryDepth);
        }

        $disableIntrospection = config('graphql.security.disable_introspection');
        if ($disableIntrospection === true) {
            $disableIntrospection = DocumentValidator::getRule('DisableIntrospection');
            /** @var \GraphQL\Validator\Rules\DisableIntrospection $disableIntrospection */
            $disableIntrospection->setEnabled(DisableIntrospection::ENABLED);
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

        $this->registerConsole();
    }

    /**
     * Register GraphQL facade
     *
     * @return void
     */
    public function registerGraphQL()
    {
        $this->app->singleton('graphql', function ($app) {
            return new GraphQL($app);
        });
    }

    /**
     * Register console commands
     *
     * @return void
     */
    public function registerConsole()
    {
        $this->commands(\Folklore\GraphQL\Console\TypeMakeCommand::class);
        $this->commands(\Folklore\GraphQL\Console\QueryMakeCommand::class);
        $this->commands(\Folklore\GraphQL\Console\MutationMakeCommand::class);
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['graphql'];
    }
}
