<?php

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Filter\Filter;

/**
 * Adds ability to create a query that will return given filters.
 */
class GivenFilters extends AbstractQuery
{
    protected $filters;

    /**
     * @param array|Filter[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function createFilters(): array
    {
        return $this->filters;
    }
}
