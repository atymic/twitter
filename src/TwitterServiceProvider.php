<?php

declare(strict_types=1);

namespace Atymic\Twitter;

use Psr\Container\ContainerExceptionInterface;

interface TwitterServiceProvider
{
    public const ASSETS_DIR = __DIR__ . '/..';
    public const PACKAGE_ALIAS = 'twitter';

    /**
     * @param mixed ...$concrete
     */
    public function set(string $name, ...$concrete): void;

    /**
     * @throws ContainerExceptionInterface
     *
     * @return mixed
     */
    public function resolve(string $name);
}
