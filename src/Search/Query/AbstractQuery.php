<?php

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Filter\Filter;

abstract class AbstractQuery
{
    /**
     * Adds the query filter to must not filter in elastic search.
     *
     * @var bool
     */
    public $scoring = false;

    /**
     * Creates elastic query filters.
     *
     * @return Filter[]
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
