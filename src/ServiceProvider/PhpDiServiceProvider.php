<?php

declare(strict_types=1);

namespace Atymic\Twitter\ServiceProvider;

use Atymic\Twitter\ApiV1\Service\Twitter as TwitterV1;
use Atymic\Twitter\ApiV1\Twitter as TwitterV1Contract;
use Atymic\Twitter\Configuration;
use Atymic\Twitter\Contract\Configuration as ConfigurationContract;
use Atymic\Twitter\Contract\GuzzleClientFactory;
use Atymic\Twitter\Contract\Querier as QuerierContract;
use Atymic\Twitter\Contract\ServiceProvider as ServiceProviderContract;
use Atymic\Twitter\Contract\Twitter as TwitterV2Contract;
use Atymic\Twitter\Factory\GuzzleClientCreator;
use Atymic\Twitter\Service\Accessor;
use Atymic\Twitter\Service\Querier;
use Atymic\Twitter\Twitter as TwitterContract;
use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpKernel\Log\Logger;

use function DI\get;

/**
 * @codeCoverageIgnore
 */
final class PhpDiServiceProvider implements ServiceProviderContract
{
    private Container $container;

    /**
     * PHPDIServiceProvider constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws Exception
     */
    protected function init(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($this->getDefinitions());

        $this->container = $containerBuilder->build();
    }

    protected function getDefinitions(): array
    {
        return [
            self::PACKAGE_ALIAS => get(TwitterContract::class),
            LoggerInterface::class => get(Logger::class),
            ConfigurationContract::class => static fn (): ConfigurationContract => Configuration::createWithDefaults(),
            GuzzleClientFactory::class => get(GuzzleClientCreator::class),
            QuerierContract::class => static function (Container $container): QuerierContract {
                $guzzleClientCreator = $container->get(GuzzleClientFactory::class);

                return new Querier(
                    $container->get(ConfigurationContract::class),
                    $guzzleClientCreator->createClient(GuzzleClientFactory::AUTH_PROTOCOL_OAUTH_1),
                    $guzzleClientCreator->createClient(GuzzleClientFactory::AUTH_PROTOCOL_OAUTH_2),
                    $container->get(LoggerInterface::class)
                );
            },
            TwitterV1Contract::class => static fn (Container $container): TwitterV1Contract => new TwitterV1(
                $container->get(QuerierContract::class)
            ),
            TwitterV2Contract::class => static fn (Container $container): TwitterV2Contract => new Accessor(
                $container->get(QuerierContract::class)
            ),
            TwitterContract::class => get(TwitterV2Contract::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, ...$concrete): void
    {
        $this->container->set($name, $concrete[0]);
    }

    /**
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $name)
    {
        return $this->container->get($name);
    }
}
