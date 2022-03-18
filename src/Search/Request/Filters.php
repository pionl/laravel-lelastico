<?php

declare(strict_types=1);

namespace Lelastico\Search\Request;

use Illuminate\Http\Request;
use Lelastico\Search\Query\AbstractSearchBuilder;

/**
 * Adds ability to prepare filters based on request and create queries to query builder.
 */
class Filters
{
    /**
     * @var array|AbstractFilter[]
     */
    protected array $filters = [];

    public function __construct(protected Request $request)
    {
    }

    /**
     * Adds a new filter that should be used applied (if can).
     *
     * @return $this
     */
    public function addFilter(AbstractFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Creates RequestQueryFilter from the array and adds them as filter. Each $createQueryFilters is keyed by the
     * request key (value is in the same format as $createQueryFilters parameter in RequestQueryFilter construct).
     *
     * @param array $requestQueryFilterMap example: $this->addQueryFilters([
     * 'citizen_of' => TermFilter::class,
     * 'phone' => [MatchFilter::class, 'phones'],
     * 'is_verified' => TermFilter::class,
     * ]);
     * @param bool  $scoring               Does filter counts to scoring?
     */
    public function addQueryFilters(array $requestQueryFilterMap, bool $scoring = false): void
    {
        foreach ($requestQueryFilterMap as $requestKey => $createQueryFilters) {
            $this->addFilter(new RequestQueryFilter($this->request, $requestKey, $createQueryFilters, $scoring));
        }
    }

    /**
     * Adds queries into query builder from applicable filters. Setups page / per_page from the request.
     */
    public function apply(AbstractSearchBuilder $builder): void
    {
        // Create queries from filters and at them to builder.
        foreach ($this->filters as $filter) {
            if ($filter->canApply() === false) {
                continue;
            }

            $builder->addQuery($filter->createQuery());
        }

        // Setup current page
        $page = $this->request->get('page');
        if (is_numeric($page) && $page > 0) {
            $builder->setCurrentPage((int) $page);
        }

        // Setup per_page page
        $perPage = $this->request->get('per_page');
        if (is_numeric($perPage) && $perPage > 0 && $perPage <= 100) {
            $builder->setPerPage((int) $perPage);
        }
    }
}
