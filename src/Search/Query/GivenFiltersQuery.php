<?php

declare(strict_types=1);

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;

/**
 * Adds ability to create a query that will return given filters.
 */
class GivenFiltersQuery extends AbstractQuery
{
    /**
     * @param array|QueryInterface[] $filters
     * @param bool           $scoring Does filters counts to scoring?
     */
    public function __construct(
        protected array $filters,
        bool $scoring = false
    ) {
        $this->scoring = $scoring;
    }

    public function createQuery(): ?QueryInterface
    {
        return new BoolQuery(filter: $this->filters);
    }
}
