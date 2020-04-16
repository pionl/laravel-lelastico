<?php

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Filter\Filter;

abstract class AbstractQuery
{
    /**
     * Creates elastic query filters.
     *
     * @return Filter[]
     */
    abstract public function createFilters(): array;
}
