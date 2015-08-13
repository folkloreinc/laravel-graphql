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
        
        if(config('graphql.routes'))
        {
            include __DIR__.'/routes.php';
        }
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
        $this->app->singleton('graphql', function($app)
        {
            return new GraphQL($app);
        });
    }
}
