<?php

namespace Lelastico\Search\Request;

use Erichard\ElasticQueryBuilder\Filter\Filter;
use Illuminate\Http\Request;
use Lelastico\Search\Query\GivenFilters;

/**
 * Adds term filter when given bool value is in the request.
 */
class BoolFilter extends RequestFilter
{
    public function __construct(Request $request, string $requestKey, string $fieldName = null)
    {
        $fieldName = is_string($fieldName) ? $fieldName : $requestKey;
        parent::__construct($request, $requestKey, function ($value) use ($fieldName) {
            return new GivenFilters([Filter::term()->setField($fieldName)->setValue($value)]);
        });

        // Ensure that value is boolean
        if ('true' === $this->value || '1' === $this->value) {
            $this->value = true;
        } elseif ('false' === $this->value || '0' === $this->value) {
            $this->value = false;
        } else {
            $this->value = null;
        }
    }

    public function canApply(): bool
    {
        return true === $this->value || false === $this->value;
    }
}
