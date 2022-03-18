<?php

namespace Lelastico\Search\Query;

use Elasticsearch\Client;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use Erichard\ElasticQueryBuilder\QueryBuilder;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Lelastico\Constants\SortDirections;
use Lelastico\Indices\AbstractElasticIndex;
use Lelastico\Search\Query\Traits\AddQueries;
use Lelastico\Search\Query\Traits\HasPaginationSettings;
use Lelastico\Search\Query\Traits\HasSorting;
use Lelastico\Search\Query\Traits\LogQuery;
use Lelastico\Search\Query\Traits\ParseResultsFromHits;
use Psr\Log\LoggerInterface;

abstract class AbstractBuilder
{
    use LogQuery;
    use HasPaginationSettings;
    use AddQueries;
    use ParseResultsFromHits;

    protected Request $request;

    public QueryBuilder $query;
    public ?array $select = null;
    /**
     * Custom client (by default is resolved by each index).
     */
    public ?Client $client = null;

    protected bool $sortById = true;

    public function __construct(Request $request, LoggerInterface $logger, Repository $config)
    {
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;

        // Create root filter that will be used as "group"
        $this->filter = new BoolQuery();

        // Prepare the underlying query builder
        $this->query = new QueryBuilder();
    }

    abstract protected function createIndex(): AbstractElasticIndex;

    /**
     * Runs a elastic query and returns Laravel's LengthAwarePaginator.
     *
     * @return LengthAwarePaginator
     *
     * @throws Exception
     */
    public function paginate(): LengthAwarePaginator
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
            $this->query->setQuery($this->filter);
        }

        // Add sort if enabled
        if (in_array(HasSorting::class, class_uses($this), true)) {
            /** @var HasSorting|AbstractBuilder $this */
            $this->addSort($this->query, $this->request);
        }

        if (true === $this->sortById) {
            $this->query->addSort('_id', SortDirections::ASC);
        }

        // Build the query and improve it
        $query = $this->query->build();

        // If the we are using collapse, the total hits is incorrect - calculate total hits for collapsed
        // entries
        if (isset($query['body']['collapse'])) {
            if (!isset($query['body']['aggs'])) {
                $query['body']['aggs'] = [];
            }

            $query['body']['aggs']['total'] = [
                'cardinality' => [
                    'field' => $query['body']['collapse']['field'],
                    'precision_threshold' => 10000 // this should be equal to max window size TODO move settings to index
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

            return new LengthAwarePaginator(
                $items,
                // Use aggregated total entries calculation or total hits
                isset($result['aggregations']['total']['value'])
                    ? $result['aggregations']['total']['value']
                    : $result['hits']['total']['value'],
                $this->perPage,
                $this->currentPage,
                [
                    'path' => $this->request->url(),
                    'query' => $this->request->query->all(),
                ]
            );
        } catch (Exception $exception) {
            if ($this->config->get('lelastico.log_failure')) {
                $this->logger->error('Elastic search failed', [
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
    public function setSelect(array $select): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Ensure that entries are by default sorted by _id to ensure correct pagination.
     */
    public function setSortById(bool $sortById): self
    {
        $this->sortById = $sortById;

        return $this;
    }

    public function isSortingById(): bool
    {
        return $this->sortById;
    }
}
