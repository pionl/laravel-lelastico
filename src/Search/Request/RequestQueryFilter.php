<?php

namespace Lelastico\Search\Request;

use Erichard\ElasticQueryBuilder\Filter\Filter;
use Exception;
use Illuminate\Http\Request;
use Lelastico\Search\Query\GivenFilters;

class RequestQueryFilter extends RequestFilter
{
    /**
     * Request filter that will create a query builder filters using $createQueryFilters closure with request value
     * only if the value is not empty.
     *
     * @param Request               $request
     * @param string                $requestKey
     * @param callable|string|array $createQueryFilters Creates a a list of query filters (instances of
     *                                                  Erichard\ElasticQueryBuilder\Filter\Filter) with given value.
     *                                                  1. You can provide callable that will create the filters
     *                                                  fn($value) =>
     *                                                  [Filter::match()->->setField()->setQuery($value)]
     *                                                  2. You can provide which query builder class to build (field
     *                                                  will be set from request key) like TermFilter::class, $value
     *                                                  will be set using setValue or setQuery.
     *                                                  3. You can provide which query builder class to build and
     *                                                  field name to use by passing an array as following example:
     *                                                  [TermFilter::class, 'test']
     * @param bool                  $scoring            Does filter counts to scoring?
     */
    public function __construct(Request $request, string $requestKey, $createQueryFilters, bool $scoring = false)
    {
        parent::__construct(
            $request,
            $requestKey,
            function ($value) use ($createQueryFilters, $requestKey, $scoring) {
                // Create builder filter
                if (is_callable($createQueryFilters)) {
                    return new GivenFilters(call_user_func($createQueryFilters, $value), $scoring);
                }

                // We need to get desired builder filter class and field name - if string is provided, then the
                // key is same as request key. Otherwise we can get the class and field name from
                // array [class, fieldName]
                if (is_string($createQueryFilters)) {
                    $builderFilterClass = $createQueryFilters;
                    $field = $requestKey;
                } elseif (is_array($createQueryFilters)) {
                    list($builderFilterClass, $field) = $createQueryFilters;
                } else {
                    throw new Exception('Un supported $createQueryFilters type. Expects: callable, string or array');
                }

                /** @var Filter $filter */
                $filter = new $builderFilterClass();

                // Does the filter support setField?
                if (method_exists($filter, 'setField')) {
                    $filter->setField($field);
                }

                // Should we set value by using setValue or setQuery?
                if (method_exists($filter, 'setValue')) {
                    $filter->setValue($value);
                } elseif (method_exists($filter, 'setQuery')) {
                    $filter->setQuery($value);
                }

                return new GivenFilters([$filter], $scoring);
            }
        );
    }
}
