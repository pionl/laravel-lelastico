<?php

namespace Lelastico\Search\Query\Traits;

trait LogQuery
{
    /**
     * Logs the query with debugbar (or any custom solution).
     *
     * @param array $result
     * @param array $query
     */
    protected function logQuery(array $result, array $query)
    {
        if (false === config('lelastico.debugbar_log')) {
            return;
        }

        add_measure('Elastic search', 0, $result['took'] / 1000);
        debugbar()->debug('Elastic search query '.json_encode($query, JSON_PRETTY_PRINT));
    }
}
