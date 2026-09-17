<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Closure;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Erichard\ElasticQueryBuilder\QueryBuilder;
use Exception;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Lelastico\Contracts\TracerContract;
use Lelastico\Entities\LazySearchEntity;
use Lelastico\Search\Query\AbstractSearchBuilder;

class LelasticoService
{
    public function __construct(
        private Client $client,
        private TracingService $tracingService,
        private LogService $logService
    ) {
    }

    public function get(AbstractSearchBuilder $builder): array
    {
        return $this->wrapSearch(
            $builder->buildForGet($builder->build()),
            $builder,
            // Build simple array with _source array values (we do not need elastic related data)
            fn (array $result): array => $builder->getResultsFromHits($result['hits']['hits'])
        );
    }

    public function paginate(
        AbstractSearchBuilder $builder,
        string $url = '',
        array $queryData = []
    ): LengthAwarePaginator {
        return $this->wrapSearch(
            $builder->buildForPagination($builder->build()),
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
            $builder->buildForAggregation($builder->build()),
            $builder,
            fn (array $result): array => $result['aggregations'] ?? []
        );
    }

    /**
     * @param array<AbstractSearchBuilder> $builders
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/future_mode.html
     */
    public function chunkAggregations(array $builders, string $summarizedMeasurementName): array
    {
        if ($builders === []) {
            return [];
        }

        /** @var array<LazySearchEntity> $searches */
        $searches = [];
        foreach ($builders as $builder) {
            $queryBuilder = $builder->buildForAggregation($builder->build());
            $query = $queryBuilder->build();

            $query['client'] = [
                'future' => 'lazy',
            ];

            $measurementName = $builder->getMeasurementName();

            $tracers = $this->tracingService->start($measurementName);

            /** @var FutureArrayInterface $result */
            $result = $this->client->search($query);
            $searches[] = new LazySearchEntity($measurementName, $query, $builder, $tracers, $result);
        }

        $tracers = $this->tracingService->start($summarizedMeasurementName);

        $aggregations = [];
        $sumTook = 0;
        foreach ($searches as $search) {
            $aggregations += $this->handleSearch(
                $search->builder,
                $search->measurementName,
                $search->query,
                $search->tracers,
                fn () => $search->result->wait(),
                function (array $result, int $took) use (&$sumTook) {
                    $sumTook += $took;

                    return $result['aggregations'];
                },
            );
        }

        $this->tracingService->finish($tracers, $sumTook);

        return $aggregations;
    }

    /**
     * @param Closure(array):mixed $buildResult
     */
    protected function wrapSearch(
        QueryBuilder $queryBuilder,
        AbstractSearchBuilder $builder,
        Closure $buildResult,
        array $clientParams = []
    ): mixed {
        $measurementName = $builder->getMeasurementName();

        $tracers = $this->tracingService->start($measurementName);

        $query = $queryBuilder->build();

        if ($clientParams !== []) {
            $query['client'] = $clientParams;
        }

        return $this->handleSearch(
            $builder,
            $measurementName,
            $query,
            $tracers,
            fn () => $this->client->search($query),
            $buildResult
        );
    }

    /**
     * @param array<TracerContract>      $tracers
     * @param Closure(): array           $getResult
     * @param Closure(array, int): mixed $handleResult Gets result + took duration
     */
    protected function handleSearch(
        AbstractSearchBuilder $builder,
        string $measurementName,
        array $query,
        array $tracers,
        Closure $getResult,
        Closure $handleResult,
    ): mixed {
        $indexName = $builder->getIndex()
            ->name;

        try {
            $result = $getResult();

            $took = (int) ($result['took'] ?? 0);

            $this->logService->logResult($measurementName, $indexName, $query, $result, $took);

            $response = $handleResult($result, $took);

            $this->tracingService->finish($tracers, $took);

            return $response;
        } catch (Exception $exception) {
            if ($exception instanceof ElasticsearchException) {
                $this->logService->logFailure($measurementName, $indexName, $query, $exception);
            }

            throw $exception;
        }
    }
}
