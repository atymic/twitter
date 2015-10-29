<?php

namespace Thujohn\Twitter;

use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app ?: app();
        $laravel_version = substr($app::VERSION, 0, strpos($app::VERSION, '.'));

        if ($laravel_version == 5) {
            $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'ttwitter');

            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('ttwitter.php'),
            ]);
        } elseif ($laravel_version == 4) {
            $this->package('thujohn/twitter', 'ttwitter', __DIR__.'/../..');
        }

        $this->app['Thujohn\Twitter\Twitter'] = $this->app->share(function ($app) {
            return new Twitter($app['config'], $app['session.store']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Thujohn\Twitter\Twitter'];
    }
}
