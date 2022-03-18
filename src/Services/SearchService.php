<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Closure;
use Elasticsearch\Client;
use Erichard\ElasticQueryBuilder\QueryBuilder;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lelastico\Search\Query\AbstractSearchBuilder;

class SearchService
{
    public function __construct(
        private Client $client,
        private TracingService $tracingService,
        private LogService $logService
    ) {
    }

    public function paginate(
        AbstractSearchBuilder $builder,
        string $url = '',
        array $queryData = []
    ): LengthAwarePaginator {
        return $this->wrapSearch(
            $builder->buildForPagination(),
            $builder,
            function (array $result) use ($builder, $url, $queryData): LengthAwarePaginator {
                // Validate the result (edge case, elastic client throws an error).
                if (isset($result['hits']) === false || isset($result['hits']['hits']) === false) {
                    return new LengthAwarePaginator([], 0, $builder->getPerPage());
                }

                // Build simple array with _source array values (we do not need elastic related data)
                $items = $builder->getResultsFromHits($result['hits']['hits']);

                return new LengthAwarePaginator(
                    $items,
                    // Use aggregated total entries calculation or total hits
                    $result['aggregations']['total']['value'] ?? $result['hits']['total']['value'],
                    $builder->getPerPage(),
                    $builder->getCurrentPage(),
                    [
                        'path' => $url,
                        'query' => $queryData,
                    ]
                );
            }
        );
    }

    /***
     * Returns aggregations array value.
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations.html#return-only-agg-results
     */
    public function aggregations(AbstractSearchBuilder $builder): array
    {
        return $this->wrapSearch(
            $builder->buildForAggregation(),
            $builder,
            fn (array $result): array => $result['aggregations'] ?? []
        );
    }

    /**
     * @param Closure(array):mixed $buildResult
     */
    protected function wrapSearch(
        QueryBuilder $queryBuilder,
        AbstractSearchBuilder $builder,
        Closure $buildResult
    ): mixed
    {
        $measurementName = $builder->getMeasurementName();
        $indexName = $builder->getIndex()
            ->name;

        $tracers = $this->tracingService->start($measurementName);

        $query = $queryBuilder->build();

        try {
            $result = $this->client->search($query);

            $took = (int) ($result['took'] ?? 0);

            $this->logService->logResult($measurementName, $indexName, $query, $result, $took);

            $response = $buildResult($result);

            $this->tracingService->finish($tracers, $took);

            return $response;
        } catch (Exception $exception) {
            $this->logService->logFailure($measurementName, $indexName, $query, $exception);

            throw $exception;
        }
    }
}
