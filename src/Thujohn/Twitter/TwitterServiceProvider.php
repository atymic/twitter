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

        $appVersion = method_exists($app, 'version') ? $app->version() : $app::VERSION;

        $laravelVersion = substr($appVersion, 0, strpos($appVersion, '.'));

        $isLumen = false;

        if (strpos(strtolower($laravelVersion), 'lumen') !== false) {
            $isLumen = true;
        }

        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'ttwitter');

        if ($isLumen) {
            $this->publishes([
                __DIR__.'/../config/config.php' => base_path('config/ttwitter.php'),
            ]);
        } else {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('ttwitter.php'),
            ]);
        }

        $this->app->singleton(Twitter::class, function () use ($app) {
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
        return ['ttwitter'];
    }
}
