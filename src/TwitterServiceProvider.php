<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class TwitterServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'twitter';

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->handleConfig();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(Twitter::class, function () {
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
        return ['twitter'];
    }

    private function handleConfig(): void
    {
        // TODO can we kill this logic?
        $app = $this->app ?: app();
        $appVersion = method_exists($app, 'version') ? $app->version() : $app::VERSION;
        $laravelVersion = substr($appVersion, 0, strpos($appVersion, '.'));

        $isLumen = false;

        if (strpos(strtolower($laravelVersion), 'lumen') !== false) {
            $isLumen = true;
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/twitter.php', self::CONFIG_KEY);

        if ($isLumen) {
            $this->publishes([
                __DIR__ . '/../config/twitter.php' => base_path(sprintf('config/%s.php', self::CONFIG_KEY)),
            ]);
        } else {
            $this->publishes([
                __DIR__ . '/../config/twitter.php' => config_path(sprintf('%s.php', self::CONFIG_KEY)),
            ]);
        }
    }
}
