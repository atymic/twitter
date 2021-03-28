<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration\PhpDi;

use Atymic\Twitter\Contract\ServiceProvider;
use Atymic\Twitter\ServiceProvider\PhpDiServiceProvider;
use Atymic\Twitter\Tests\Integration\ResolutionTest;
use Atymic\Twitter\Twitter;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class TwitterTest extends TestCase implements ResolutionTest
{
    /**
     * @var ServiceProvider
     */
    private $serviceProvider;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = new PhpDiServiceProvider();
        $this->serviceProvider->initContainer();
    }

    public function testTwitterResolution(): void
    {
        $instance = $this->serviceProvider->resolve(Twitter::class);

        self::assertInstanceOf(Twitter::class, $instance);
    }

    public function testTwitterResolutionViaAlias(): void
    {
        $instance = $this->serviceProvider->resolve(ServiceProvider::PACKAGE_ALIAS);

        self::assertInstanceOf(Twitter::class, $instance);
    }
}
