<?php

declare(strict_types=1);

namespace Lelastico\Tests;

use Elasticsearch\Client;
use Lelastico\Contracts\IndicesServiceContract;
use Lelastico\LelasticoServiceProvider;
use Lelastico\Services\IndicesService;
use Orchestra\Testbench\TestCase;

class LelasticoServiceProviderTest extends TestCase
{
    public function testItRegistersAndBootsThePackage(): void
    {
        self::assertTrue($this->app->providerIsLoaded(LelasticoServiceProvider::class));
        self::assertTrue($this->app->bound(Client::class));
        self::assertInstanceOf(IndicesService::class, $this->app->make(IndicesServiceContract::class));
        self::assertContains(
            $this->app->configPath('lelastico.php'),
            LelasticoServiceProvider::pathsToPublish(LelasticoServiceProvider::class)
        );
    }

    protected function getPackageProviders($app): array
    {
        return [LelasticoServiceProvider::class];
    }
}
