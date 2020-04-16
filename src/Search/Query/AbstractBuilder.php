<?php

namespace Lelastico\Search\Query;

use Elasticsearch\Client;
use Erichard\ElasticQueryBuilder\Filter\Filter;
use Erichard\ElasticQueryBuilder\QueryBuilder;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Lelastico\Indices\AbstractElasticIndex;
use Lelastico\Search\Query\Traits\AddQueries;
use Lelastico\Search\Query\Traits\HasPaginationSettings;
use Lelastico\Search\Query\Traits\LogQuery;
use Lelastico\Search\Query\Traits\ParseResultsFromHits;

abstract class AbstractBuilder
{
    use LogQuery;
    use HasPaginationSettings;
    use AddQueries;
    use ParseResultsFromHits;

    /**
     * @var QueryBuilder
     */
    public $query;

    /**
     * @var array|null
     */
    public $select = null;

    /**
     * Custom client (by default is resolved by each index).
     *
     * @var Client|null
     */
    public $client = null;

    /**
     * AvailabilitySearchBuilder constructor.
     */
    public function __construct()
    {
        // Create root filter that will be used as "group"
        $this->filter = Filter::bool();

        // Prepare the underlying query builder
        $this->query = new QueryBuilder();
    }

    abstract protected function createIndex(): AbstractElasticIndex;

    /**
     * Runs a elastic query and returns laravel's LengthAwarePaginator.
     *
     * @return LengthAwarePaginator
     *
     * @throws Exception
     */
    public function paginate()
    {
        // Determine the index
        $index = $this->createIndex();

        // Setup basic information
        $this->query->setIndex($index->name);
        $this->query->setSize($this->perPage);
        // Setup from by calculating current page
        $this->query->setFrom($this->perPage * ($this->currentPage - 1));

        if (null !== $this->select) {
            $this->query->setSource($this->select);
        }

        // Add filter only if it contains any filters.
        if (false === $this->filter->isEmpty()) {
            $this->query->addFilter($this->filter);
        }

        // Build the query and improve it
        $query = $this->query->getQuery();

        // If the we are using collapse, the total hits is incorrect - calculate total hits for collapsed
        // entries
        if (isset($query['body']['collapse'])) {
            if (!isset($query['body']['aggs'])) {
                $query['body']['aggs'] = [];
            }

            $query['body']['aggs']['total'] = [
                'cardinality' => [
                    'field' => $query['body']['collapse']['field'],
                ],
            ];
        }

        // TODO: send execution time
        try {
            $result = $index->client->search($query);

            // Validate the result (edge case, elastic client throws an error).
            if (false === isset($result['hits']) || false === isset($result['hits']['hits'])) {
                return new LengthAwarePaginator(collect(), 0, $this->perPage);
            }

            // Log query
            $this->logQuery($result, $query);

            // Build simple array with _source array values (we do not need elastic related data)
            $items = $this->getResultsFromHits($result['hits']['hits']);

            // Return array
            return new LengthAwarePaginator(
                $items,
                // Use aggregated total entries calculation or total hits
                isset($result['aggregations']['total']['value'])
                    ? $result['aggregations']['total']['value']
                    : $result['hits']['total']['value'],
                $this->perPage,
                $this->currentPage,
                ['path' => request()->fullUrl()]
            );
        } catch (Exception $exception) {
            if (config('lelastico.log_failure')) {
                logger('Elastic search failed', [
                    'error' => $exception->getMessage(),
                    'query' => $query,
                ]);
            }
            throw $exception;
        }
    }

    /**
     * @param array|null $select
     *
     * @return AbstractBuilder
     */
    public function setSelect(array $select)
    {
        $this->select = $select;

        return $this;
    }
}
