<?php

declare(strict_types=1);

namespace Atymic\Twitter\ServiceProviders;

use Atymic\Twitter\Configuration;
use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider;
use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use function DI\get;

/**
 * @codeCoverageIgnore
 */
final class PhpDiTwitterServiceProvider implements TwitterServiceProvider
{
    /**
     * @var Container
     */
    private $container;

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
            self::PACKAGE_ALIAS => get(Twitter::class),
            Twitter::class => static function (): Twitter {
                return new Twitter(Configuration::createWithDefaults());
            },
        ];
    }
}
