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
     * Add queries from given filters. Allows to create pass fresh filters instance like:
     * (new MyBuilder())
     *      ->addQueriesFromFilters(new MyFilters())
     *      ->paginate().
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
     * Adds query filters to the elastic query builder using addMust (if $query->scoring is true) or addFilter.
     *
     * @param AbstractQuery $query
     *
     * @return $this
     */
    public function addQuery(AbstractQuery $query): self
    {
        foreach ($query->createFilters() as $filter) {
            if ($query->scoring) {
                $this->filter->addMust($filter);
            } else {
                $this->filter->addFilter($filter);
            }
        }

        return $this;
    }
}
