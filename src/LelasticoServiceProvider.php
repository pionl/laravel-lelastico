<?php

namespace Lelastico;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Lelastico\Console\UpdateIndicesCommand;

class LelasticoServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $configDefaultFilePath;
    /**
     * @var string
     */
    protected $configFileName;
    /**
     * @var string
     */
    protected $configName;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->configName = 'lelastico';
        $this->configFileName = $this->configName.'.php';
        $this->configDefaultFilePath = __DIR__.'/../config/'.$this->configFileName;
    }

    public function register()
    {
        $this->app->singleton(Client::class, function () {
            return ClientBuilder::create()
                                ->setHosts(config('lelastico.hosts'))
                                ->build();
        });

        // Merge config
        $this->mergeConfigFrom(
            $this->configDefaultFilePath, $this->configName
        );
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
