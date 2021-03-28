<?php

declare(strict_types=1);

namespace Atymic\Twitter\ServiceProvider;

use Atymic\Twitter\ApiV1\Contract\Twitter as TwitterV1Contract;
use Atymic\Twitter\ApiV1\Service\Twitter as TwitterV1;
use Atymic\Twitter\Configuration;
use Atymic\Twitter\Contract\Configuration as ConfigurationContract;
use Atymic\Twitter\Contract\Http\ClientFactory;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Contract\ServiceProvider as ServiceProviderContract;
use Atymic\Twitter\Contract\Twitter as TwitterV2Contract;
use Atymic\Twitter\Http\Factory\ClientCreator;
use Atymic\Twitter\Service\Accessor;
use Atymic\Twitter\Service\Querier;
use Atymic\Twitter\Twitter as TwitterContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Psr\Container\ContainerExceptionInterface;

/**
 * @codeCoverageIgnore
 */
final class LaravelServiceProvider extends ServiceProvider implements ServiceProviderContract
{
    private const CONFIG_KEY = 'twitter';

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->handleConfig();
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

    /**
     * Register the service provider.
     *
     * @throws LogicException
     */
    public function register(): void
    {
        $this->app->alias(TwitterContract::class, self::PACKAGE_ALIAS);
        $this->app->singleton(
            ConfigurationContract::class,
            static fn () => Configuration::createFromConfig(config(self::CONFIG_KEY))
        );
        $this->app->singleton(ClientFactory::class, ClientCreator::class);
        $this->app->singleton(QuerierContract::class, Querier::class);
        $this->app->singleton(TwitterV1Contract::class, TwitterV1::class);
        $this->app->singleton(TwitterV2Contract::class, Accessor::class);
        $this->app->singleton(
            TwitterContract::class,
            static function (Application $app) {
                $config = $app->get(ConfigurationContract::class);

                if ($config->getApiVersion() !== TwitterContract::API_VERSION_1) {
                    return $app->get(TwitterV2Contract::class);
                }

                return $app->get(TwitterV1Contract::class);
            }
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [TwitterContract::class];
    }

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
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
}
