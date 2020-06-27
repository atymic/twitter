<?php

declare(strict_types=1);

namespace Atymic\Twitter\ServiceProviders;

use Atymic\Twitter\Configuration;
use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider as ServiceProviderContract;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

final class LaravelTwitterServiceProvider extends ServiceProvider implements ServiceProviderContract
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
     *
     * @throws LogicException
     */
    public function register(): void
    {
        $this->app->alias(Twitter::class, self::PACKAGE_ALIAS);
        $this->app->singleton(
            Twitter::class,
            static function () {
                return new Twitter(
                    Configuration::fromLaravelConfiguration(config(self::CONFIG_KEY)),
                    app(LoggerInterface::class)
                );
            }
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Twitter::class];
    }

    /**
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function resolve(string $name)
    {
        return $this->app->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, ...$concrete): void
    {
        $this->app->bind($name, ...$concrete);
    }

    private function handleConfig(): void
    {
        $app = $this->app ?: app();
        $appVersion = method_exists($app, 'version') ? $app->version() : $app::VERSION;
        $laravelVersion = strtolower(substr($appVersion, 0, strpos($appVersion, '.')));
        $configFile = sprintf('%s/config/twitter.php', self::ASSETS_DIR);
        $isLumen = stripos($laravelVersion, 'lumen') !== false;

        $this->mergeConfigFrom($configFile, self::CONFIG_KEY);
        $this->publishes(
            [
                $configFile => $isLumen
                    ? base_path(sprintf('config/%s.php', self::CONFIG_KEY))
                    : config_path(sprintf('%s.php', self::CONFIG_KEY)),
            ]
        );
    }
}
