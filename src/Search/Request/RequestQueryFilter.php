<?php

namespace Lelastico\Search\Request;

use Closure;
use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;
use Exception;
use Illuminate\Http\Request;
use Lelastico\Search\Query\GivenFilters;

class RequestQueryFilter extends RequestFilter
{
    /**
     * Request filter that will create a query builder filters using $createQueryFilters closure with request value
     * only if the value is not empty.
     *
     * @param Request                        $request
     * @param string                         $requestKey
     * @param Closure(mixed): QueryInterface $createQueryFilters You can provide callable that will create the filters
     *                                                           fn($value) =>
     *                                                           [new TermQuery($field, $value)]
     * @param bool                           $scoring            Does filter counts to scoring?
     */
    public function __construct(Request $request, string $requestKey, Closure $createQueryFilters, bool $scoring = false)
    {
        parent::__construct(
            $request,
            $requestKey,
            function ($value) use ($createQueryFilters, $requestKey, $scoring) {
                return new GivenFilters(call_user_func($createQueryFilters, $value), $scoring);
            }
        );
    }
}
