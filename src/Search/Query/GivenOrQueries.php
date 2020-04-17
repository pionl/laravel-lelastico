<?php

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Filter\Filter;

/**
 * Adds ability to create a list of filters based from list  queries with or condition.
 */
class GivenOrQueries extends AbstractQuery
{
    /**
     * @var AbstractQuery[]|array
     */
    public $queries;

    /**
     * @param array|AbstractQuery[] $queries List of queries that will be grouped into should filter
     * @param bool                  $scoring Does filters counts to scoring?
     */
    public function __construct(array $queries, bool $scoring = false)
    {
        $this->queries = $queries;
        $this->scoring = $scoring;
    }

    public function createFilters(): array
    {
        $boolFilter = Filter::bool();
        // Loop all queries and create should filter
        foreach ($this->queries as $query) {
            foreach ($query->createFilters() as $filter) {
                $boolFilter->addShould($filter);
            }
        }

        return [$boolFilter];
    }
}
