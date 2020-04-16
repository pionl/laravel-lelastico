<?php

namespace Lelastico\Search\Query\Traits;

use Erichard\ElasticQueryBuilder\Filter\BoolFilter;
use Lelastico\Search\Query\AbstractQuery;
use Lelastico\Search\Request\Filters;

trait AddQueries
{
    /**
     * Bool filter that is in the root of the query.
     *
     * @var BoolFilter
     */
    protected $filter;

    /**
     * Add queries from given filters.
     *
     * @param Filters $filters
     *
     * @return $this
     */
    public function addQueriesFromFilters(Filters $filters): self
    {
        /* @var AbstractQuery $this */
        $filters->apply($this);

        return $this;
    }

    /**
     * Adds query filters to the elastic query builder.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function addQuery(AbstractQuery $query): self
    {
        foreach ($query->createFilters() as $filter) {
            $this->filter->addFilter($filter);
        }

        return $this;
    }

    /**
     * Adds query must filters (used for scoring) to the elastic query builder.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function addMustQuery(AbstractQuery $query)
    {
        foreach ($query->createFilters() as $filter) {
            $this->filter->addMust($filter);
        }

        return $this;
    }
}
