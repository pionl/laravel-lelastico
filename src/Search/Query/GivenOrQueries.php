<?php

declare(strict_types=1);

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;

/**
 * Adds ability to create a list of filters based from list  queries with or condition.
 */
class GivenOrQueries extends AbstractQuery
{
    /**
     * @param array|AbstractQuery[] $queries List of queries that will be grouped into should filter
     * @param bool                  $scoring Does filters counts to scoring?
     */
    public function __construct(
        public array $queries,
        bool $scoring = false
    ) {
        $this->scoring = $scoring;
    }

    public function createQuery(): ?QueryInterface
    {
        $boolFilter = new BoolQuery();
        // Loop all queries and create should filter
        foreach ($this->queries as $query) {
            $elasticQuery = $query->createQuery();

            if ($elasticQuery instanceof \Erichard\ElasticQueryBuilder\Contracts\QueryInterface === false) {
                continue;
            }

            $boolFilter->addShould($elasticQuery);
        }

        if ($boolFilter->isEmpty()) {
            return null;
        }

        return $boolFilter;
    }
}
