<?php namespace Folklore\GraphQL;

use Illuminate\Support\Facades\Facade;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Get the active router.
     *
     * @return Router
     */
    protected function getRouter()
    {
        return $this->app;
    }
    
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
        $configPath = __DIR__ . '/../../config';
        $viewsPath = __DIR__.'/../../resources/views';
        $this->mergeConfigFrom($configPath . '/config.php', 'graphql');
        $this->loadViewsFrom($viewsPath, 'graphql');
    }

    /**
     * Register facade
     *
     * @return void
     */
    public function registerGraphQL()
    {
        // Check if facades are activated
        if (Facade::getFacadeApplication() == $this->app) {
            class_alias(\Folklore\GraphQL\Support\Facades\GraphQL::class, 'GraphQL');
        }

        parent::registerGraphQL();
    }

    /**
     * Register the helper command to publish the config file
     */
    public function registerConsole()
    {
        parent::registerConsole();
        
        $this->commands(\Folklore\GraphQL\Console\PublishCommand::class);
    }
}
