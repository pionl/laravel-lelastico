<?php

namespace Lelastico\Search\Request;

use Illuminate\Http\Request;
use Lelastico\Search\Query\AbstractQuery;

class RequestFilter extends AbstractFilter
{
    /**
     * @var string|null
     */
    public $value;

    /**
     * @var string
     */
    public $requestKey;

    /**
     * Used for building a query with the value.
     *
     * @var string|callable
     */
    public $queryClass;

    /**
     * Request filter that will create a query of given class with request value only if the value is not empty.
     *
     * @param Request         $request
     * @param string          $requestKey resolves value from the $request under given key
     * @param string|callable $queryClass Creates a query of given class with value as first argument (provide closure
     *                                    that will create query, first argument contains value).
     *                                    Example: fn($value) => new NameQuery($value)
     */
    public function __construct(Request $request, string $requestKey, $queryClass)
    {
        $this->value = $request->get($requestKey);
        $this->requestKey = $requestKey;
        $this->queryClass = $queryClass;
    }

    public function createQuery(): AbstractQuery
    {
        if (is_callable($this->queryClass)) {
            return call_user_func($this->queryClass, $this->value);
        }

        return new $this->queryClass($this->value);
    }

    public function canApply(): bool
    {
        return !empty($this->value);
    }
}
