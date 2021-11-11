<?php

namespace Lelastico\Search\Query\Traits;

use Illuminate\Contracts\Config\Repository;
use Psr\Log\LoggerInterface;

trait LogQuery
{
    protected LoggerInterface $logger;
    protected Repository $config;

    /**
     * Logs the query.
     *
     * @param array $result
     * @param array $query
     */
    protected function logQuery(array $result, array $query)
    {
        $time = $result['took'] / 1000; // ms

        $isDebug = $this->config->get('lelastico.log_debug');

        if (false === $isDebug) {
            if (true == $this->config->get('lelastico.log_measurement')) {
                $this->logMeasurement($time, $query['index'] ?? 'unknown');
            }

            return;
        }

        // Debug-bar add_measure function
        $this->logDebug($time, $query);
    }

    protected function logMeasurement(float $time, string $index): void
    {
        $this->logger->info('Elastic search query time', [
            'took' => $time,
            'index' => $index,
        ]);
    }

    protected function logDebug(float $time, array $query): void
    {
        if (true === function_exists('add_measure')) {
            add_measure('Elastic search', 0, $time);
        }

        $this->logger->debug('Elastic search query', [
            'took' => $time,
            'query' => $query,
        ]);
    }
}
