<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Exception;
use Lelastico\Configs\LelasticoConfig;
use Lelastico\Contracts\LogServiceContract;
use Psr\Log\LoggerInterface;

class LogService implements LogServiceContract
{
    public function __construct(
        private LoggerInterface $logger,
        private LelasticoConfig $config
    ) {
    }

    public function logResult(string $measurementName, string $index, array $query, array $result, int $took): void
    {
        if ($this->config->debugLogEnabled() === false) {
            return;
        }

        $this->logger->debug(
            'elasticsearch.query:' . $measurementName . ' took ' . $took . ' ms',
            [
                'measurement_name' => $measurementName,
                'index' => $index,
                'query' => $query,
                'took' => $took,
            ]
        );
    }

    public function logFailure(string $measurementName, string $index, array $query, Exception $exception): void
    {
        if ($this->config->failureLogEnabled() === false) {
            return;
        }

        $this->logger->error(
            'elasticsearch.query:' . $measurementName . ' failed',
            [
                'measurement_name' => $measurementName,
                'index' => $index,
                'query' => $query,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]
        );
    }
}
