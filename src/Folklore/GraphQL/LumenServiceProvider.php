<?php namespace Folklore\GraphQL;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Get the active router.
     *
     * @return Router
     */
    protected function getRouter()
    {
        return property_exists($this->app, 'router') ? $this->app->router : $this->app;
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
     * Bootstrap router
     *
     * @return void
     */
    protected function bootRouter()
    {
        $router = $this->getRouter();

        // Define routes
        if ($this->app['config']->get('graphql.routes')) {
            include __DIR__.'/routes.php';
        }
    }

    /**
     * Register the helper command to publish the config file
     */
    public function registerCommands()
    {
        parent::registerCommands();

        $this->app->singleton('command.graphql.publish', function ($app) {
            return new \Folklore\GraphQL\Console\PublishCommand($app['files']);
        });

        $this->commands('command.graphql.publish');
    }
}
