<?php

namespace Lelastico;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Lelastico\Console\UpdateIndicesCommand;
use Lelastico\Contracts\IndicesServiceContract;

class LelasticoServiceProvider extends ServiceProvider
{
    const CONFIG_NAME = 'lelastico';

    /**
     * @var string
     */
    protected $configDefaultFilePath;
    /**
     * @var string
     */
    protected $configFileName;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->configFileName = self::CONFIG_NAME.'.php';
        $this->configDefaultFilePath = __DIR__.'/../config/'.$this->configFileName;
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            $this->configDefaultFilePath, self::CONFIG_NAME
        );

        $repositoryConfig = $this->app->get(Repository::class);

        $this->app->singleton(Client::class, function () use ($repositoryConfig) {
            return ClientBuilder::create()
                ->setHosts($repositoryConfig->get(self::CONFIG_NAME.'.hosts'))
                ->build();
        });

        $serviceFromConfig = $repositoryConfig->get(self::CONFIG_NAME.'.service');
        $this->app->singleton(IndicesServiceContract::class, $serviceFromConfig);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateIndicesCommand::class,
            ]);
        }

        // Publish config options
        $this->publishes([
            $this->configDefaultFilePath => config_path($this->configFileName),
        ]);
    }
}
