<?php

declare(strict_types=1);

namespace Lelastico;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Lelastico\Configs\LelasticoConfig;
use Lelastico\Console\UpdateIndicesCommand;
use Lelastico\Contracts\IndicesServiceContract;
use Lelastico\Services\IndicesService;

class LelasticoServiceProvider extends ServiceProvider
{
    protected const CONFIG_FILE_NAME = LelasticoConfig::NAME . '.php';

    protected const CONFIG_FILE_PATH = __DIR__ . '/../config/' . self::CONFIG_FILE_NAME;

    public function register(): void
    {
        parent::register();

        // Merge config
        $this->mergeConfigFrom(self::CONFIG_FILE_PATH, LelasticoConfig::NAME);

        /** @var LelasticoConfig $config */
        $config = $this->app->make(LelasticoConfig::class);

        $this->app->singleton(Client::class, fn () => ClientBuilder::create()
            ->setHosts($config->getHosts())
            ->build());

        $this->app->singleton(IndicesServiceContract::class, IndicesService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([UpdateIndicesCommand::class]);
        }

        // Publish config options
        $this->publishes([
            self::CONFIG_FILE_PATH => $this->app->configPath(self::CONFIG_FILE_NAME),
        ]);
    }
}
