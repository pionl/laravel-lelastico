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
     * @param bool           $scoring Does filters counts to scoring?
     */
    public function __construct(array $filters, bool $scoring = false)
    {
        $this->filters = $filters;
        $this->scoring = $scoring;
    }

    public function createFilters(): array
    {
        return $this->filters;
    }
}
