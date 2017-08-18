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
        app()->environment('production') ? $this->bootProduction() : $this->bootDefault();
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
        static $registred = false;
        // Check if facades are activated
        if (Facade::getFacadeApplication() == $this->app && !$registred) {
            class_alias(\Folklore\GraphQL\Support\Facades\GraphQL::class, 'GraphQL');
            $registred = true;
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

    /**
     * Bootstrap when config env is Production
     * the schemas and type will be loaded as needed
     * production close graphiql
     * production close preload type and Schemas
     * production close graphiql view
     * production open security
     */
    public function bootProduction()
    {
        $this->bootPublishes();

        app('config')->set('graphql.graphiql', null);

        $this->bootRouter();

        $this->bootSecurity();
    }

    /**
     * Bootstrap when disable config env
     */
    public function bootDefault()
    {
        $this->bootPublishes();

        $this->bootTypes();

        $this->bootSchemas();

        $this->bootRouter();

        $this->bootViews();
    }
}
