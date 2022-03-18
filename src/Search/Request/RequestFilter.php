<?php

declare(strict_types=1);

namespace Lelastico\Search\Request;

use Closure;
use Illuminate\Http\Request;
use Lelastico\Search\Query\AbstractQuery;

class RequestFilter extends AbstractFilter
{
    public mixed $value = null;

    /**
     * Request filter that will create a query of given class with request value only if the value is not empty.
     *
     * @param string  $requestKey  resolves value from the $request under given key
     * @param Closure $createQuery Creates a query of given class with value as first argument (provide closure
     * that will create query, first argument contains value). fn($value) => new
     * MyQueyr($value)
     */
    public function __construct(
        Request $request,
        public string $requestKey,
        public Closure $createQuery
    ) {
        $this->value = $this->convertValue($request->get($requestKey));
    }

    public function createQuery(): AbstractQuery
    {
        return call_user_func($this->createQuery, $this->value);
    }

    public function canApply(): bool
    {
        return empty($this->value) === false;
    }

    /**
     * Adds ability to change the value type.
     */
    protected function convertValue(mixed $value): mixed
    {
        return $value;
    }
}
