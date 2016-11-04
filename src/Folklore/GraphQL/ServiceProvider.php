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
        $this->bootEvents();
        
        $this->bootPublishes();
        
        $this->bootTypes();
        
        $this->bootSchemas();
        
        $this->bootRouter();
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
        
        $this->mergeConfigFrom($configPath.'/config.php', 'graphql');
        
        $this->publishes([
            $configPath.'/config.php' => config_path('graphql.php'),
        ], 'config');
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
