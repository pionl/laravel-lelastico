<?php

namespace Lelastico\Search\Query\Traits;

use Illuminate\Contracts\Config\Repository;
use Psr\Log\LoggerInterface;

trait LogQuery
{
    protected LoggerInterface $logger;
    protected Repository $config;

    /**
     * Logs the query with debugbar (or any custom solution).
     *
     * @param array $result
     * @param array $query
     */
    protected function logQuery(array $result, array $query)
    {
        if (false === $this->config->get('lelastico.debugbar_log')) {
            return;
        }

        // TODO refactor
        add_measure('Elastic search', 0, $result['took'] / 1000);
        debugbar()->debug('Elastic search query '.json_encode($query, JSON_PRETTY_PRINT));
    }
}
