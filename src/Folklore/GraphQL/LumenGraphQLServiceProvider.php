<?php namespace Folklore\GraphQL;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

class LumenGraphQLServiceProvider extends ServiceProvider
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

        if (config('graphql.routes')) {
            include __DIR__ . '/lumen_routes.php';
        }
    }

    /**
     * Bootstrap publishes
     *
     * @return void
     */
    protected function bootPublishes()
    {
        $configPath = __DIR__ . '/../../config';

        $this->mergeConfigFrom($configPath . '/config.php', 'graphql');
    }

    /**
     * Bootstrap publishes
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
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGraphQL();
        $this->registerCommands();

        // create an alias for Laravels BaseController to Lumens
        class_alias(\Laravel\Lumen\Routing\Controller::class, 'Illuminate\Routing\Controller');
    }

    /**
     * Register panneau
     *
     * @return void
     */
    public function registerGraphQL()
    {
        if (Facade::getFacadeApplication() == app()) { // check if facades are activated
            class_alias(\Folklore\GraphQL\Support\Facades\GraphQL::class, 'GraphQL');
        }

        $this->app->singleton('graphql', function ($app) {
            return new GraphQL($app);
        });
    }

    /**
     * Register the helper command to publish the config file
     */
    public function registerCommands()
    {
        $this->commands([
            \Folklore\GraphQL\Commands\PublishCommand::class
        ]);
    }
}
