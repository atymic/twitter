<?php

declare(strict_types=1);

namespace Atymic\Twitter\Contract;

use Psr\Container\ContainerExceptionInterface;

interface ServiceProvider
{
    public const ASSETS_DIR = __DIR__ . '/../..';
    public const PACKAGE_ALIAS = 'twitter';

    /**
     * @param mixed ...$concrete
     */
    public function set(string $name, ...$concrete): void;

    /**
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $name);
}
