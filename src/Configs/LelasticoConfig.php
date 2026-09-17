<?php

declare(strict_types=1);

namespace Lelastico\Configs;

use const false;
use Lelastico\Contracts\TracerContract;
use Lelastico\Exceptions\ConfigException;

class LelasticoConfig extends AbstractConfig
{
    final public const NAME = 'lelastico';

    protected const KEY_TRACING = 'tracing';

    public function getIndices(): array
    {
        $indices = $this->get('indices', []);

        if (is_array($indices) === false) {
            throw new ConfigException('indices must be array');
        }

        return $indices;
    }

    /**
     * @return array<string>
     */
    public function getHosts(): array
    {
        $hosts = $this->get('hosts');

        if (is_array($hosts) === false) {
            throw new ConfigException('hosts not defined');
        }

        if ($hosts === []) {
            throw new ConfigException('hosts are empty');
        }

        return $hosts;
    }

    /**
     * @return array<class-string<TracerContract>>
     */
    public function getTracers(): array
    {
        $hosts = $this->get([self::KEY_TRACING, 'tracers'], []);

        if (is_array($hosts) === false) {
            throw new ConfigException('tracers must be an array');
        }

        return $hosts;
    }

    public function isTracingEnabled(): bool
    {
        $shouldReplace = $this->get([self::KEY_TRACING, 'enabled'], true);

        if (is_bool($shouldReplace) === false) {
            throw new ConfigException('enabled for tracing must be a boolean');
        }

        return $shouldReplace;
    }

    public function shouldReplaceTracers(): bool
    {
        return $this->getBool([self::KEY_TRACING, 'replace'], false);
    }

    public function debugLogEnabled(): bool
    {
        return $this->getBool('log_debug', true);
    }

    public function failureLogEnabled(): bool
    {
        return $this->getBool('log_failure', true);
    }

    protected function getConfigFileName(): string
    {
        return self::NAME;
    }

    protected function getBool(array|string $keyOrPath, bool $default): bool
    {
        $shouldReplace = $this->get($keyOrPath, $default);

        if (is_bool($shouldReplace) === false) {
            throw new ConfigException('replace for traces must be a boolean');
        }

        return $shouldReplace;
    }
}
