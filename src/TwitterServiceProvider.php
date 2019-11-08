<?php

namespace Atymic\Twitter;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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
        // TODO can we kill this logic?
        $app = $this->app ?: app();
        $appVersion = method_exists($app, 'version') ? $app->version() : $app::VERSION;
        $laravelVersion = substr($appVersion, 0, strpos($appVersion, '.'));

        $isLumen = false;

        if (strpos(strtolower($laravelVersion), 'lumen') !== false) {
            $isLumen = true;
        }

        $this->mergeConfigFrom(__DIR__ . '/config/twitter.php', 'twitter');

        if ($isLumen) {
            $this->publishes([
                __DIR__ . '/config/twitter.php' => base_path('config/twitter.php'),
            ]);
        } else {
            $this->publishes([
                __DIR__ . '/config/twitter.php' => config_path('twitter.php'),
            ]);
        }

        $this->app->bind(Twitter::class, function () {
            return new Twitter(
                Configuration::fromLaravelConfiguration(config('twitter')),
                app(LoggerInterface::class)
            );
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
