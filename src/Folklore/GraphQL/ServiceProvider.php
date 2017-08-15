<?php namespace Folklore\GraphQL;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Config $config, Router $router, ViewFactory $viewFactory)
    {
        $this->bootPublishes();

        $this->bootRouter($config, $router);

        $this->bootViews($config, $viewFactory);
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

    /**
     * Register GraphQL facade
     *
     * @return void
     */
    protected function registerGraphQL()
    {
        $this->app->singleton('graphql', function ($app) {

            $graphql = new GraphQL($app);
            $config = $app->make('config');

            $this->addTypes($config, $graphql);

            $this->addSchemas($config, $graphql);

            $this->registerEventListeners($app->make('events'), $app->make('router'), $graphql);

            $this->applySecurityRules();

            return $graphql;
        });
    }

    /**
     * Add types from config
     *
     * @param Config $config
     * @param GraphQL $graphql
     * @return void
     */
    protected function addTypes(Config $config, GraphQL $graphql)
    {
        $types = $config->get('graphql.types', []);

        foreach ($types as $name => $type) {
            $graphql->addType($type, is_numeric($name) ? null : $name);
        }
    }

    /**
     * Add schemas from config
     *
     * @param Config $config
     * @param GraphQL $graphql
     * @return void
     */
    protected function addSchemas(Config $config, GraphQL $graphql)
    {
        $schemas = $config->get('graphql.schemas', []);

        foreach ($schemas as $name => $schema) {
            $graphql->addSchema($name, $schema);
        }
    }

    /**
     * Bootstrap events
     *
     * @param EventDispatcher $events
     * @param Router $router
     * @param GraphQL $graphql
     * @return void
     */
    protected function registerEventListeners(EventDispatcher $events, Router $router, GraphQL $graphql)
    {
        // Update the schema route pattern when schema is added
        $events->listen(Events\SchemaAdded::class, function () use ($router, $graphql) {
            $schemaNames = array_keys($graphql->getSchemas());
            $router->pattern('graphql_schema', '('.implode('|', $schemaNames).')');
        });
    }

    /**
     * Configure security from config
     *
     * @return void
     */
    protected function applySecurityRules()
    {
        $maxQueryComplexity = config('graphql.security.query_max_complexity');
        if ($maxQueryComplexity !== null) {
            /** @var QueryComplexity $queryComplexity */
            $queryComplexity = DocumentValidator::getRule('QueryComplexity');
            $queryComplexity->setMaxQueryComplexity($maxQueryComplexity);
        }

        $maxQueryDepth = config('graphql.security.query_max_depth');
        if ($maxQueryDepth !== null) {
            /** @var QueryDepth $queryDepth */
            $queryDepth = DocumentValidator::getRule('QueryDepth');
            $queryDepth->setMaxQueryDepth($maxQueryDepth);
        }

        $disableIntrospection = config('graphql.security.disable_introspection');
        if ($disableIntrospection === true) {
            /** @var DisableIntrospection $disableIntrospection */
            $disableIntrospection = DocumentValidator::getRule('DisableIntrospection');
            $disableIntrospection->setEnabled(DisableIntrospection::ENABLED);
        }
    }

    /**
     * Register console commands
     *
     * @return void
     */
    protected function registerConsole()
    {
        $this->commands(Console\TypeMakeCommand::class);
        $this->commands(Console\QueryMakeCommand::class);
        $this->commands(Console\MutationMakeCommand::class);
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
     * Bootstrap router
     *
     * @return void
     */
    protected function bootRouter(Config $config, Router $router)
    {
        if ($config->get('graphql.routes')) {
            include __DIR__.'/routes.php';
        }
    }

    /**
     * Bootstrap Views
     *
     * @param Config $config
     * @param ViewFactory $viewFactory
     * @return void
     */
    protected function bootViews(Config $config, ViewFactory $viewFactory)
    {
        if ($config->get('graphql.graphiql', true)) {
            $view = $config->get('graphql.graphiql.view', 'graphql::graphiql');
            $viewFactory->composer($view, View\GraphiQLComposer::class);
        }
    }
}
