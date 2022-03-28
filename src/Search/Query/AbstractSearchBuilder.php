<?php

declare(strict_types=1);

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Aggregation\AbstractAggregation;
use Erichard\ElasticQueryBuilder\Aggregation\CardinalityAggregation;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use Erichard\ElasticQueryBuilder\QueryBuilder;
use Lelastico\Constants\SortDirections;
use Lelastico\Indices\AbstractElasticIndex;
use Lelastico\Search\Query\Traits\AddQueries;
use Lelastico\Search\Query\Traits\HasPaginationSettings;
use Lelastico\Search\Query\Traits\ParseResultsFromHits;

/**
 * @template Index of AbstractElasticIndex
 */
abstract class AbstractSearchBuilder
{
    use HasPaginationSettings;
    use AddQueries;
    use ParseResultsFromHits;

    public ?array $select = null;

    protected bool $sortById = true;

    private AbstractElasticIndex $index;

    /**
     * @var array<string, AbstractAggregation> Aggregations by name
     */
    private array $aggregations = [];

    private ?string $measurementName = null;

    public function __construct(
        protected BuilderDependencies $dependencies
    ) {
        // Create root filter that will be used as "group"
        $this->query = new BoolQuery();
        $this->index = $this->dependencies->container->make($this->getIndexClass());
    }

    public function build(): QueryBuilder
    {
        // Prepare the underlying query builder
        $builder = $this->dependencies->createQueryBuilderAction->execute();

        // Add filter only if it contains any filters.
        if ($this->query->isEmpty() === false) {
            $builder->setQuery($this->query);
        }

        // Add filter only if it contains any filters.
        if ($this->aggregations !== []) {
            foreach ($this->aggregations as $aggregation) {
                $builder->addAggregation($aggregation);
            }
        }

        // Setup basic information
        $builder->setIndex($this->index->name);

        return $builder;
    }

    public function buildForPagination(QueryBuilder $builder): QueryBuilder
    {
        $builder->setSize($this->perPage);
        // Setup from by calculating current page
        $builder->setFrom($this->perPage * ($this->currentPage - 1));

        if ($this->select !== null) {
            $source = $builder->getSource() ?? [];
            if (is_array($source)) {
                $builder->setSource(array_merge($source, $this->select));
            } else {
                $builder->setSource($this->select);
            }
        }

        if ($this->sortById) {
            $this->applySortById($builder);
        }

        if ($builder->getCollapse() !== null) {
            $builder->addAggregation((new CardinalityAggregation(
                'total', // TODO constants?
                $builder->getCollapse()
                    ->getField(),
            ))->setPrecisionThreshold(10000)); // this should be equal to max window size TODO move settings to index
        }

        return $builder;
    }

    public function buildForAggregation(QueryBuilder $builder): QueryBuilder
    {
        // Setup basic information
        $builder->setSize(0);

        return $builder;
    }

    public function setSelect(?array $select): self
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

    /**
     * @return AbstractElasticIndex|mixed
     */
    public function getIndex(): mixed
    {
        return $this->index;
    }

    public function getQuery(): BoolQuery
    {
        return $this->query;
    }

    public function addAggregation(AbstractAggregation $aggregation): self
    {
        $this->aggregations[$aggregation->getName()] = $aggregation;

        return $this;
    }

    public function getAggregation(string $name): ?AbstractAggregation
    {
        return $this->aggregations[$name] ?? null;
    }

    /**
     * @return array<string, AbstractAggregation>
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    public function setMeasurementName(string $measurementName): self
    {
        $this->measurementName = $measurementName;

        return $this;
    }

    public function getMeasurementName(): string
    {
        if ($this->measurementName === null) {
            $this->measurementName = $this->getDefaultMeasurementName();
        }

        return $this->measurementName;
    }

    public function getDefaultMeasurementName(): string
    {
        $parts = explode('\\', static::class);
        return end($parts);
    }

    /**
     * @return class-string<Index>
     */
    abstract protected function getIndexClass(): string;

    protected function applySortById(QueryBuilder $builder): void
    {
        $builder->addSort('_id', SortDirections::ASC);
    }
}
