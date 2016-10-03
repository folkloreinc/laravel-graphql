<?php namespace Folklore\GraphQL;

use Illuminate\Support\ServiceProvider;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPublishes();
        
        $this->bootTypes();
        
        $this->bootSchemas();
        
        if (config('graphql.routes')) {
            $this->bootEvents();
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
            $schemas = array_keys($this->app['graphql']->getSchemas());
            $this->app['router']->pattern('graphql_schema', '('.implode('|', $schemas).')');
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
    }
    
    /**
     * Register panneau
     *
     * @return void
     */
    public function registerGraphQL()
    {
        $this->app->singleton('graphql', function ($app) {
            return new GraphQL($app);
        });
    }
}
