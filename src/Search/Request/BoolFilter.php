<?php

declare(strict_types=1);

namespace Lelastico\Search\Request;

use Erichard\ElasticQueryBuilder\Query\TermsQuery;
use Illuminate\Http\Request;
use Lelastico\Search\Query\GivenFiltersQuery;

/**
 * Adds term filter when given bool value is in the request.
 */
class BoolFilter extends RequestFilter
{
    public function __construct(Request $request, string $requestKey, string $fieldName = null)
    {
        $fieldName = is_string($fieldName) ? $fieldName : $requestKey;
        parent::__construct($request, $requestKey, fn ($value) => new GivenFiltersQuery([
            new TermsQuery($fieldName, $value),
        ]));

        // Ensure that value is boolean
        if ($this->value === 'true' || $this->value === '1') {
            $this->value = true;
        } elseif ($this->value === 'false' || $this->value === '0') {
            $this->value = false;
        } else {
            $this->value = null;
        }
    }

    public function canApply(): bool
    {
        return $this->value === true || $this->value === false;
    }
}
