<?php

declare(strict_types=1);

namespace Lelastico\Configs;

use Illuminate\Config\Repository;

abstract class AbstractConfig
{
    private string $configFileName;

    public function __construct(private Repository $config)
    {
        $this->configFileName = $this->getConfigFileName();
    }

    abstract protected function getConfigFileName(): string;

    /**
     * @param string[]|string $keyOrPath Provide path that will be joined to dot notation
     */
    protected function get(string|array $keyOrPath, mixed $default = ''): mixed
    {
        if (is_array($keyOrPath)) {
            $keyOrPath = implode('.', $keyOrPath);
        }

        return $this->config->get($this->configFileName . '.' . $keyOrPath, $default);
    }
}
