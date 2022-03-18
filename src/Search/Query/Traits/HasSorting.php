<?php

declare(strict_types=1);

namespace Lelastico\Search\Query\Traits;

use Erichard\ElasticQueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lelastico\Constants\SortDirections;

/**
 * Adds sortable behavior to the query builder from the request "sort" query parameter.
 */
trait HasSorting
{
    /**
     * Allowed fields for sorting.
     *
     * Key is the name of the field in the query. Value is the name of the field in the index.
     *
     * [ 'goals' => 'goals_count' 'minutes' => 'played_minutes' ]
     */
    abstract public function allowedSortFields(): array;

    protected function addSort(QueryBuilder $query, Request $request): void
    {
        if ($request->missing('sort')) {
            return;
        }

        $request->validate($this->getValidationRules());

        foreach ($request->get('sort') as $sort) {
            $exploded = explode(':', $sort);

            $field = $this->allowedSortFields()[$exploded[0]];
            $direction = $exploded[1] ?? SortDirections::ASC;

            $query->addSort($field, $direction);
        }
    }

    private function getSortFields(): array
    {
        $values = [];

        foreach (array_keys($this->allowedSortFields()) as $field) {
            $values[] = $field;
            foreach (SortDirections::getAll() as $direction) {
                $values[] = sprintf('%s:%s', $field, $direction);
            }
        }

        return $values;
    }

    private function getValidationRules(): array
    {
        return [
            'sort' => ['required', 'array'],
            'sort.*' => ['required', 'string', Rule::in($this->getSortFields())],
        ];
    }
}
