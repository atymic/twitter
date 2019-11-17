<?php

declare(strict_types=1);

namespace Atymic\Twitter\Tests\Integration;

use Atymic\Twitter\Twitter;
use Atymic\Twitter\TwitterServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTempDirectory());
        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [TwitterServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'twitter' => Twitter::class,
        ];
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory() . '/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    private function getTempDirectory($suffix = ''): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'temp' . ($suffix === '' ? '' : DIRECTORY_SEPARATOR . $suffix);
    }

    /**
     * @param string $directory
     *
     * @return bool
     */
    private function initializeDirectory(string $directory): bool
    {
        if (File::isDirectory($directory)) {
            return File::cleanDirectory($directory);
        }

        return File::makeDirectory($directory);
    }

    /**
     * @param Application $app
     */
    private function setUpDatabase($app): void
    {
        file_put_contents($this->getTempDirectory() . '/database.sqlite', null);

        $this->artisan('migrate')->run();
    }
}
