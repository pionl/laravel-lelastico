<?php

declare(strict_types=1);

namespace Lelastico\Search\Query\Traits;

use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use Lelastico\Search\Query\AbstractQuery;
use Lelastico\Search\Request\Filters;

trait AddQueries
{
    /**
     * Bool filter that is in the root of the query.
     */
    public readonly BoolQuery $query;

    /**
     * Add queries from given filters. Allows to create pass fresh filters instance like:
     * (new MyBuilder())
     *      ->addQueriesFromFilters(new MyFilters())
     *      ->paginate().
     */
    public function addQueriesFromFilters(Filters $filters): self
    {
        $filters->apply($this);

        return $this;
    }

    /**
     * Adds query filters to the elastic query builder using addMust (if $query->scoring is true) or addFilter.
     */
    public function addQuery(AbstractQuery $query): self
    {
        $elasticQuery = $query->createQuery();

        if ($elasticQuery instanceof QueryInterface === false) {
            return $this;
        }

        if ($query->scoring) {
            $this->query->addMust($elasticQuery);
        } else {
            $this->query->addFilter($elasticQuery);
        }

        return $this;
    }
}
