<?php

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Query\BoolQuery;

abstract class AbstractQuery
{
    /**
     * Adds the query filter to must not filter in elastic search.
     */
    public bool $scoring = false;

    /**
     * @return array<BoolQuery>
     */
    abstract public function createFilters(): array;

    /**
     * Does filter counts to scoring?
     *
     * @param bool $scoring
     *
     * @return AbstractQuery
     */
    public function setScoring(bool $scoring): AbstractQuery
    {
        $this->scoring = $scoring;

        return $this;
    }
}
